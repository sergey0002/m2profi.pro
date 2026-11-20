import json, os, sys, tempfile, subprocess, unittest, hashlib, fcntl
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "control"))
from worker import run, Stop
from directory_status import describe


class Control(unittest.TestCase):
    def git(self, p, *args):
        return (
            subprocess.check_output(
                ["git", "-C", str(p), *args], stderr=subprocess.PIPE
            )
            .decode()
            .strip()
        )

    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.addCleanup(self.tmp.cleanup)
        self.p = Path(self.tmp.name)
        old = os.environ["HOME"]
        os.environ["HOME"] = str(self.p / "home")
        self.addCleanup(os.environ.__setitem__, "HOME", old)
        Path.home().mkdir()
        self.live = self.p / "site"
        self.live.mkdir()
        self.remote = self.p / "remote"
        self.dev = self.p / "dev"
        self.git(self.p, "init", "--bare", str(self.remote))
        self.git(self.p, "init", "-b", "main", str(self.dev))
        self.git(self.dev, "config", "user.name", "dev")
        self.git(self.dev, "config", "user.email", "dev@example.org")
        (self.dev / "code.txt").write_text("one\n")
        self.git(self.dev, "add", ".")
        self.git(self.dev, "commit", "-m", "base")
        self.git(self.dev, "push", str(self.remote), "main")
        self.base = self.git(self.dev, "rev-parse", "HEAD")
        self.q = dict(
            path=str(self.live),
            repo=str(self.remote),
            branch="main",
            name="fromserver",
            email="from@server.ru",
            allow=["src/*"],
            write_branches=["main"],
            maintenance=True,
            target="test",
        )

    def op(self, a, **kw):
        return run(dict(self.q, action=a, **kw))

    def bind(self):
        self.op("bind", token=self.op("preview")["token"])

    def refs(self):
        return self.git(self.remote, "show-ref")

    def test_card_branch_changes_only_on_publish(self):
        def card(): return describe(dict(path=str(self.live),guard=True),str(self.remote))
        self.assertEqual(card()['state'],'none');self.bind();self.assertEqual(card()['state'],'none')
        self.op('deploy');self.assertEqual(card()['branch'],'main');self.git(self.dev,'push',str(self.remote),'main:other')
        self.op('sync',branch='other');self.assertEqual(card()['branch'],'main')
        self.op('deploy',branch='other');self.assertEqual(card()['branch'],'other')
        h=next((Path.home()/'.dep-control').iterdir());(h/'incomplete.json').write_text('{}');self.assertEqual(card()['state'],'incomplete')

    def test_card_legacy_detached(self):
        def card(): return describe(dict(path=str(self.live),guard=False),str(self.remote))
        self.git(self.live,'init','-b','staging');self.assertEqual(card()['state'],'none')
        self.git(self.live,'fetch',str(self.remote),'main');self.git(self.live,'checkout','-B','staging','FETCH_HEAD');self.assertEqual(card()['branch'],'staging')
        self.git(self.live,'checkout','--detach','HEAD');self.assertEqual(card()['state'],'unknown')

    def test_empty_sync_loop(self):
        self.bind()
        self.assertFalse(list(self.live.iterdir()))
        self.assertRaises(Stop, self.op, "sync")
        self.op("deploy")
        self.assertEqual((self.live / "code.txt").read_text(), "one\n")
        (self.live / "code.txt").write_text("ftp\n")
        (self.live / "src").mkdir()
        (self.live / "src/new").write_text("add")
        self.assertIn("M  code", self.op("check")["message"])
        self.op("sync")
        self.op("deploy")
        before = self.refs()
        self.op("deploy")
        self.assertEqual(before, self.refs())
        self.assertEqual(self.git(self.remote, "show", "main:src/new"), "add")
        self.assertIn(
            "fromserver <from@server.ru>",
            self.git(self.remote, "log", "--format=%an <%ae>", "main"),
        )

    def test_conflict_resolution(self):
        (self.live / "code.txt").write_text("ftp\n")
        self.bind()
        (self.dev / "code.txt").write_text("remote\n")
        self.git(self.dev, "commit", "-am", "dev")
        self.git(self.dev, "push", str(self.remote), "main")
        self.assertRaisesRegex(Stop, "конфликт", self.op, "deploy")
        self.assertEqual((self.live / "code.txt").read_text(), "ftp\n")
        branches = self.git(
            self.remote,
            "for-each-ref",
            "--format=%(refname)",
            "refs/heads/ftp-snapshots/",
        )
        self.git(self.dev, "fetch", str(self.remote), branches)
        self.git(self.dev, "merge", "-s", "ours", "--no-edit", "FETCH_HEAD")
        self.git(self.dev, "push", str(self.remote), "main")
        self.op("deploy")
        self.assertEqual((self.live / "code.txt").read_text(), "remote\n")

    def test_wrong_repo_and_branch_policy(self):
        self.git(self.live, "clone", "-b", "main", str(self.remote), ".")
        self.git(self.live, "remote", "set-url", "origin", "wrong")
        self.assertRaisesRegex(Stop, "origin", self.op, "status")
        self.git(self.live, "remote", "set-url", "origin", str(self.remote))
        (self.live / "code.txt").write_text("ftp")
        before = self.refs()
        self.assertRaisesRegex(
            Stop, "FTP_WRITE_BRANCHES", self.op, "deploy", write_branches=["test"]
        )
        self.assertEqual(before, self.refs())

    def test_initial_missing(self):
        (self.live / ".env").write_text("private")
        v = self.op("preview")
        self.assertTrue(v["missing"])
        self.assertRaisesRegex(Stop, "удаления", self.op, "bind", token=v["token"])
        self.assertEqual((self.live / ".env").read_text(), "private")

    def test_preview_lock_partial(self):
        (self.live / "code.txt").write_text("ftp")
        v = self.op("preview")
        (self.live / "code.txt").write_text("new")
        self.assertRaisesRegex(Stop, "изменилось", self.op, "bind", token=v["token"])
        self.bind()
        h = next((Path.home() / ".dep-control").iterdir())
        with (h / "lock").open() as f:
            fcntl.flock(f, fcntl.LOCK_EX)
            self.assertRaisesRegex(Stop, "занята", self.op, "deploy")
        (h / "incomplete.json").write_text("{}")
        self.assertRaisesRegex(Stop, "Незавершённая", self.op, "sync")

    def test_clone_delete_and_restrict(self):
        self.git(self.live, "clone", "-b", "main", str(self.remote), ".")
        self.assertTrue(self.op("status")["bound"])
        (self.live / "code.txt").unlink()
        (self.live / "oops").write_text("x")
        self.assertRaisesRegex(Stop, "БЛОК", self.op, "sync")
        (self.live / "oops").unlink()
        (self.live / ".env").write_text("private")
        self.op("deploy")
        self.assertEqual((self.live / ".env").read_text(), "private")
        self.assertEqual(self.git(self.live, "status", "--porcelain"), "?? .env")
        self.assertEqual(self.git(self.remote, "ls-tree", "main"), "")


if __name__ == "__main__":
    unittest.main()
