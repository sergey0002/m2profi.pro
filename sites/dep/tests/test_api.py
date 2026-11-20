"""PHP API -> fixed SSH transport -> worker, all in an isolated temp folder."""

import json, os, shutil, subprocess, tempfile, unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


class Api(unittest.TestCase):
    def setUp(self):
        tmp = tempfile.TemporaryDirectory()
        self.addCleanup(tmp.cleanup)
        self.p = p = Path(tmp.name)
        home = p / "home"
        home.mkdir()
        self.env = dict(
            os.environ, HOME=str(home), PATH=str(p) + ":" + os.environ["PATH"]
        )
        (p / "projects").mkdir()
        shutil.copy(ROOT / "ajax.php", p)
        shutil.copytree(ROOT / "control", p / "control")
        live = p / "live"
        live.mkdir()
        (p / "projects/demo.env").write_text(
            f"DIRS=pilot:{live}|test,legacy:{p}/other|test,live:{p}/prod|prod\nALLOWED_USERS=demo\nFTP_CONTROL_TARGETS=pilot\nSSH_HOST=fixture.invalid\nSSH_USER=fixture\nREPO_URL=https://github.com/example/demo.git\n"
        )
        shim = p / "ssh"
        shim.write_text("#!/bin/sh\nexec python3 -I -\n")
        shim.chmod(0o755)

    def post(self, **k):
        req = dict(project="demo", target="pilot", action="fc_status", csrf="test")
        req.update(k)
        code = "session_start();$_SESSION=['fc_csrf'=>'test','user'=>'demo'];session_write_close();$_POST=json_decode($argv[1],true);require 'ajax.php';"
        r = subprocess.run(
            ["php", "-r", code, json.dumps(req)],
            cwd=self.p,
            env=self.env,
            capture_output=True,
            check=True,
        )
        self.assertFalse(r.stderr, r.stderr)
        return json.loads(r.stdout)

    def test_card_permissions(self):
        r = self.post(action='directory_statuses');self.assertEqual(r['status'],'ok')
        self.assertEqual({v['name']:v['state'] for v in r['targets']},{'pilot':'none','legacy':'unavailable'})
        self.assertEqual(self.post(action='directory_statuses',csrf='')['status'],'error')

    def test_route(self):
        self.assertFalse(self.post()["bound"])
        self.assertIn("CSRF", self.post(csrf="invalid")["message"])
        self.assertEqual(self.post(target="legacy")["status"], "error")
        self.assertEqual(self.post(target="live")["status"], "error")
        self.assertIn(
            "Не указаны",
            self.post(target="legacy", action="deploy", csrf="")["message"],
        )
        self.assertIn("CSRF", self.post(action="deploy", csrf="")["message"])


if __name__ == "__main__":
    unittest.main()
