"""Protected TEST-target operations. Sent on stdin to python3 on the deployment host."""

import base64
import fcntl
import fnmatch
import hashlib
import json
import os
from pathlib import Path
import re
import stat
import subprocess
import tempfile
import time
import uuid


class Stop(Exception):
    pass


def need(cond, msg):
    if not cond:
        raise Stop(msg)


def save(path, obj):
    with tempfile.NamedTemporaryFile(dir=path.parent, mode="w", delete=False) as f:
        json.dump(obj, f)
        f.flush()
        os.fsync(f.fileno())
    os.replace(f.name, path)
    fd = os.open(path.parent, os.O_RDONLY)
    try:
        os.fsync(fd)
    finally:
        os.close(fd)


def digest(data):
    return hashlib.sha1(b"blob " + str(len(data)).encode() + b"\0" + data).hexdigest()


def protected(name):
    parts = name.lower().split("/")
    return any(
        x
        in {
            ".git",
            ".ssh",
            ".env",
            "logs",
            "cache",
            "uploads",
            "backups",
            "node_modules",
            "vendor",
            ".htpasswd",
            ".npmrc",
            ".netrc",
        }
        or x.startswith(".env.")
        or x.endswith((".key", ".pem", ".p12", ".log", ".sql", ".bak", ".zip"))
        for x in parts
    )


def run(q):
    os.umask(0o077)
    live = Path(q["path"])
    need(
        live.is_absolute()
        and live.is_dir()
        and live.resolve() == live
        and live != Path("/"),
        "Цель должна быть отдельной директорией без ссылок.",
    )
    for other in q.get("other", []):
        p = Path(other).resolve()
        need(
            live != p and live not in p.parents and p not in live.parents,
            "Пересечение путей целей.",
        )
    root = Path.home() / ".dep-control"
    need(
        root.resolve() == root
        and live not in root.parents
        and root != live
        and root not in live.parents,
        "Недопустимое размещение служебной папки.",
    )
    key = hashlib.sha256(str(live).encode()).hexdigest()[:24]
    home = root / key
    home.mkdir(mode=0o700, parents=True, exist_ok=True)
    lock = (home / "lock").open("a")
    try:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
    except BlockingIOError:
        lock.close()
        raise Stop("Цель занята. Повторите после завершения операции.")
    # Keep the lock alive until all commands and state updates have finished.
    try:
        if q["action"] in ("bind", "sync", "deploy"):
            run_id = uuid.uuid4().hex
            entry = dict(
                id=run_id,
                user=q.get("user"),
                action=q["action"],
                target=q["target"],
                started=time.time(),
                status="running",
            )
            logs = home / "runs"
            logs.mkdir(exist_ok=True)
            jf = logs / (run_id + ".json")
            save(jf, entry)
            try:
                result = locked(q, live, home, key)
                entry.update(status="ok", message=result["message"])
                result["run"] = run_id
                return result
            except Exception as e:
                entry.update(
                    status="error",
                    message=str(e) if isinstance(e, Stop) else "Системная ошибка",
                )
                raise
            finally:
                entry["finished"] = time.time()
                save(jf, entry)
        return locked(q, live, home, key)
    finally:
        lock.close()


def locked(q, live, home, key):
    a = q["action"]
    url = q["repo"]
    branch = q.get("branch", "")
    repo = home / "repo"
    sf = home / "state.json"
    journal = home / "operations.jsonl"
    env = dict(
        os.environ,
        GIT_TERMINAL_PROMPT="0",
        GIT_AUTHOR_NAME=q["name"],
        GIT_AUTHOR_EMAIL=q["email"],
        GIT_COMMITTER_NAME=q["name"],
        GIT_COMMITTER_EMAIL=q["email"],
        LC_ALL="C",
    )

    def git(*args, cwd=None, check=True, data=None):
        cmd = [
            "git",
            "-c",
            "core.hooksPath=/dev/null",
            "-c",
            "commit.gpgsign=false",
            "-c",
            "gc.auto=0",
            *args,
        ]
        r = subprocess.run(
            cmd,
            cwd=cwd or repo,
            env=env,
            input=data if data is not None else b"",
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            timeout=100,
        )
        if check and r.returncode:
            raise Stop(
                "Git: ошибка операции "
                + args[0]
                + ". Проверьте доступ к репозиторию, место на диске и состояние ветки."
            )
        return r

    def text(*args, **kw):
        return git(*args, **kw).stdout.decode().strip()

    def writable(b):
        return any(fnmatch.fnmatchcase(b, g) for g in q.get("write_branches", []))

    def log(msg):
        with journal.open("a") as f:
            f.write(
                json.dumps([time.time(), q.get("user"), a, msg], ensure_ascii=False)
                + "\n"
            )

    def result(msg, **kw):
        return dict(status="ok", message=msg, **kw)

    state = json.loads(sf.read_text()) if sf.exists() else {}
    if state:
        need(
            state["repo"] == url,
            "Конфигурация репозитория изменилась. Требуется ручная проверка.",
        )
    partial = home / "incomplete.json"
    pending = partial.exists()

    # A genuine clone may be adopted using its checked-out revision, never an enclosing repo.
    def livehead():
        if not (live / ".git").exists():
            need(
                git("rev-parse", "--git-dir", cwd=live, check=False).returncode != 0,
                "Цель находится внутри другой рабочей копии.",
            )
            return ""
        need(
            (live / ".git").is_dir() and not (live / ".git").is_symlink(),
            "Связанные worktree и ссылки не поддерживаются.",
        )
        need(
            Path(text("rev-parse", "--show-toplevel", cwd=live)).resolve() == live
            and text("remote", "get-url", "origin", cwd=live).removesuffix(".git")
            == url.removesuffix(".git"),
            "Чужая рабочая копия или origin.",
        )
        need(
            not any(
                (live / ".git" / n).exists()
                for n in (
                    "MERGE_HEAD",
                    "CHERRY_PICK_HEAD",
                    "rebase-merge",
                    "rebase-apply",
                    "index.lock",
                )
            ),
            "Незавершённая операция в рабочей копии.",
        )
        need(
            git("diff", "--cached", "--quiet", cwd=live, check=False).returncode == 0,
            "В индексе цели есть отдельные изменения.",
        )
        return text("rev-parse", "HEAD", cwd=live)

    if pending:
        raise Stop(
            "Незавершённая выкладка. Автокоммиты заблокированы; проверьте incomplete.json на хостинге и восстановите цель по инструкции."
        )
    head = livehead()
    if state:
        need(head == state.get("head", ""), "HEAD рабочей копии изменён извне.")
    if a == "status":
        return result(
            "Привязано к Git" if state or head else "Не привязано",
            bound=bool(state or head),
            empty=state.get("empty", False),
        )
    if a == "branches":
        lines = text("ls-remote", "--heads", url, cwd=home).splitlines()
        names = [
            x.split("\t")[1][11:]
            for x in lines
            if "\trefs/heads/" in x and "\trefs/heads/ftp-snapshots/" not in x
        ]
        return result("Ветки", branches=sorted(names))
    if a not in ("check",):
        need(
            branch
            and len(branch) < 220
            and not branch.startswith(("-", "ftp-snapshots/"))
            and git(
                "check-ref-format", "refs/heads/" + branch, cwd=home, check=False
            ).returncode
            == 0,
            "Недопустимая ветка.",
        )
    if not repo.exists():
        git("init", "-q", str(repo), cwd=home)
    if not state or a != "check":
        git("fetch", "--no-tags", "--prune", url, "+refs/heads/*:refs/remotes/source/*")
    dst = (
        text("rev-parse", "--verify", "refs/remotes/source/" + branch + "^{commit}")
        if a != "check"
        else ""
    )
    if a in ("preview", "bind") and q.get("base_oid"):
        oid = q["base_oid"]
        need(
            isinstance(oid, str)
            and re.fullmatch("[a-fA-F0-9]{40}", oid)
            and git("merge-base", "--is-ancestor", oid, dst, check=False).returncode
            == 0,
            "Базовый SHA не принадлежит истории выбранной ветки.",
        )
        dst = text("rev-parse", oid + "^{commit}")
    if head and not state:
        need(
            bool(text("branch", "-r", "--contains", head)),
            "HEAD цели не найден в истории remote.",
        )
        state = dict(
            repo=url, base=head, snap=head, head=head, empty=False, active=False
        )
        save(sf, state)
    bound = bool(state)
    need(bound or a in ("preview", "bind"), "Сначала привяжите цель к Git.")
    if a in ("preview", "bind"):
        need(not bound, "Цель уже привязана.")
    base = state.get("snap", dst)

    def tree(sha):
        out = {}
        for rec in git("ls-tree", "-rz", sha).stdout.split(b"\0"):
            if not rec:
                continue
            meta, path = rec.split(b"\t", 1)
            mode, kind, oid = meta.decode().split()
            n = path.decode()
            need(
                kind == "blob" and mode in ("100644", "100755") and not protected(n),
                "Репозиторий содержит защищённый путь, ссылку или вложенный репозиторий: "
                + n,
            )
            out[n] = (mode, oid)
        return out

    def checked_path(n):
        p = live / n
        need(p.resolve() == p and live in p.parents, "Неподдерживаемая ссылка: " + n)
        return p

    def fileval(p):
        st = p.lstat()
        need(
            stat.S_ISREG(st.st_mode) and st.st_size <= 32 * 1024 * 1024,
            "Необычный или слишком крупный файл: " + str(p.relative_to(live)),
        )
        data = p.read_bytes()
        return ("100755" if st.st_mode & 0o100 else "100644", digest(data))

    def scan(tr):
        now = {}
        unknown = []
        extra = []
        skipped = []

        def fail(e):
            raise Stop("Невозможно прочитать каталог цели.")

        for path, dirs, files in os.walk(live, onerror=fail):
            folder = Path(path)
            keep = []
            for d in dirs:
                n = (folder / d).relative_to(live).as_posix()
                if n == ".git":
                    continue
                if d == ".git":
                    unknown.append(n)
                    continue
                if protected(n):
                    skipped.append(n)
                    continue
                if (folder / d).is_symlink():
                    unknown.append(n)
                    continue
                keep.append(d)
            dirs[:] = keep
            for f in files:
                p = folder / f
                n = p.relative_to(live).as_posix()
                if protected(n):
                    skipped.append(n)
                    continue
                if n in tr:
                    now[n] = fileval(checked_path(n))
                else:
                    extra.append(n)
        if extra:
            raw = b"\0".join(x.encode() for x in extra) + b"\0"
            r = git(
                "--git-dir=" + str(repo / ".git"),
                "--work-tree=" + str(live),
                "check-ignore",
                "--no-index",
                "--stdin",
                "-z",
                cwd=live,
                data=raw,
                check=False,
            )
            need(r.returncode in (0, 1), "Ошибка правил игнорирования.")
            ignored = set(r.stdout.decode().split("\0"))
            for n in extra:
                if n in ignored:
                    skipped.append(n)
                    continue
                if not any(fnmatch.fnmatchcase(n, g) for g in q["allow"]):
                    unknown.append(n)
                    continue
                now[n] = fileval(checked_path(n))
        need(
            all(
                not any(ord(c) < 32 for c in n)
                for n in set(now) | set(tr) | set(unknown)
            ),
            "Недопустимые управляющие символы в имени файла.",
        )
        changes = {
            n: ("A" if n not in tr else "D" if n not in now else "M")
            for n in sorted(set(now) | set(tr))
            if now.get(n) != tr.get(n)
        }
        return now, changes, sorted(unknown), sorted(skipped)

    def report(ch, unk, skip):
        return "\n".join(
            [
                f"{len(ch)} изм.; вне разрешённых путей: {len(unk)}; исключено: {len(skip)}"
            ]
            + [v + "  " + k for k, v in list(ch.items())[:100]]
            + ["БЛОК  " + n for n in unk[:40]]
            + ["Исключено  " + n for n in skip[:20]]
        )

    empty = not any(live.iterdir())
    tr = tree(base)
    now, ch, unk, skip = scan(tr)
    if a in ("preview", "bind"):
        token = hashlib.sha256(
            json.dumps(
                [url, branch, dst, empty, sorted(now.items()), unk, skip],
                sort_keys=True,
            ).encode()
        ).hexdigest()
        msg = (
            (
                "Пустая цель: первое заполнение только при деплое."
                if empty
                else "Подтвердите базовый SHA. D означает отсутствие файла; при синхронизации это станет удалением.\n"
                + report(ch, unk, skip)
            )
            + "\nБаза: "
            + dst
        )
        if a == "preview":
            return result(msg, token=token, missing=(not empty and "D" in ch.values()))
        need(
            empty or "D" not in ch.values() or q.get("accept_missing") is True,
            "Подтвердите первоначальные удаления отдельно.",
        )
        need(
            empty or len(ch) <= 100,
            "Слишком много первоначальных отличий для безопасного подтверждения. Уточните базовую ревизию.",
        )
        need(q.get("token") == token, "Состояние изменилось. Повторите предпросмотр.")
        need(not unk, "Есть неразрешённые файлы. Уточните состав цели.")
        save(sf, dict(repo=url, base=dst, snap=dst, empty=empty, head="", active=False, published_branch=None))
        log("bind " + dst)
        return result("Привязано к Git: " + dst)
    initial_scan = (now, ch, unk, skip)
    if state["empty"]:
        need(
            empty,
            "После привязки в пустой папке появились файлы. Требуется ручная сверка; запись остановлена.",
        )
        if a == "check":
            return result("Пустая цель. Ожидается первая выкладка.")
        need(a == "deploy", "Пустая цель: синхронизация до первой выкладки запрещена.")
        ch = {}
    if a == "check":
        return result(report(ch, unk, skip) if ch or unk or skip else "Правок нет.")
    need(a in ("deploy", "sync"), "Недопустимая операция.")
    need(not unk, "Операция заблокирована.\n" + report(ch, unk, skip))
    need(
        q.get("maintenance") is True,
        "Подтвердите окно обслуживания и остановку внешней записи.",
    )
    log("start " + branch + " " + dst)
    # Use an isolated index: never stage anything from the site's own index.
    if (
        ch
        or state["active"]
        and git(
            "merge-base", "--is-ancestor", state["snap"], dst, check=False
        ).returncode
        != 0
    ):
        need(
            writable(branch),
            "Для записи в эту ветку нет разрешения FTP_WRITE_BRANCHES. Сайт и remote не изменены.",
        )
    if ch:
        git("read-tree", base)
        for n, kind in ch.items():
            if kind == "D":
                git("update-index", "--force-remove", "--", n)
                continue
            data = checked_path(n).read_bytes()
            mode, oid = now[n]
            need(
                text("hash-object", "-w", "--stdin", data=data) == oid,
                "Файл изменился при фиксации.",
            )
            git("update-index", "--add", "--cacheinfo", mode, oid, n)
        snap = text(
            "commit-tree",
            text("write-tree"),
            "-p",
            base,
            "-m",
            "Server edits: " + q["target"],
        )
        ref = "ftp-snapshots/" + key + "/" + snap
        git("push", url, snap + ":refs/heads/" + ref)
        state.update(snap=snap, active=True)
        save(sf, state)
        log("snapshot " + snap)
    snap = state["snap"]
    ref = "ftp-snapshots/" + key + "/" + snap
    if (
        state["active"]
        and git("merge-base", "--is-ancestor", snap, dst, check=False).returncode != 0
    ):
        git("checkout", "--detach", "--force", dst)
        r = git("merge", "--no-edit", "--no-ff", snap, check=False)
        if r.returncode:
            names = text("diff", "--name-only", "--diff-filter=U")
            git("merge", "--abort", check=False)
            raise Stop(
                "Объединение остановлено. Разрешите конфликт merge-коммитом в "
                + branch
                + " с "
                + ref
                + " ("
                + snap
                + "). Сайт не изменён.\n"
                + names
            )
        dst = text("rev-parse", "HEAD")
        r = git("push", url, dst + ":refs/heads/" + branch, check=False)
        need(
            not r.returncode,
            "Ветка изменилась или push запрещён. Снимок: " + ref + "; сайт не изменён.",
        )
    if a == "sync":
        log("sync " + dst)
        return result(
            "Синхронизация завершена. Ветка: "
            + branch
            + " @ "
            + dst
            + ("\nСнимок: " + ref if state["active"] else "\nНовых коммитов нет.")
        )
    # Stage and validate before changing any public files. TEST maintenance publication.
    final = tree(dst)
    old = {} if state["empty"] else now
    again = scan(tr)
    need(again == initial_scan, "Каталог изменился; публикация отменена.")
    need(not state["empty"] or not any(live.iterdir()), "Пустая цель изменилась.")
    with tempfile.TemporaryDirectory(dir=home) as tmp:
        stage = Path(tmp)
        changed = [n for n in final if final[n] != old.get(n)]
        removed = sorted(set(old) - set(final))
        for n in changed:
            p = checked_path(n)
            need(
                not p.exists() or n in old and p.is_file(),
                "Коллизия при публикации: " + n,
            )
            need(
                all(not v.exists() or v.is_dir() for v in p.parents),
                "Путь занят файлом: " + n,
            )
            blob = git("cat-file", "blob", final[n][1]).stdout
            dest = stage / n
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_bytes(blob)
        pre_dirs = {p for n in changed for p in (live / n).parents if p.exists()}
        for n in removed:
            checked_path(n)
        need(scan(tr) == again, "FTP изменился перед публикацией.")
        save(partial, dict(before=state, desired=dst, branch=branch))
        for n in changed:
            p = checked_path(n)
            p.parent.mkdir(parents=True, exist_ok=True, mode=0o755)
            # New public directories must be traversable; do not use the private state umask here.
            ancestor = p.parent
            while ancestor != live and ancestor not in pre_dirs:
                ancestor.chmod(0o755)
                ancestor = ancestor.parent
            perm = stat.S_IMODE(p.stat().st_mode) & 0o666 if p.exists() else 0o644
            with tempfile.NamedTemporaryFile(
                dir=p.parent, prefix=".dep-tmp-", delete=False
            ) as f:
                f.write((stage / n).read_bytes())
                os.fchmod(f.fileno(), perm | (0o111 if final[n][0] == "100755" else 0))
                f.flush()
                os.fsync(f.fileno())
            os.replace(f.name, p)
        for n in removed:
            checked_path(n).unlink()
        if head and dst != head:
            git("fetch", "--no-tags", str(repo), dst, cwd=live)
            git("update-ref", "--no-deref", "HEAD", dst, head, cwd=live)
            git("read-tree", dst, cwd=live)
        verified, _, block, _ = scan(final)
        need(
            verified == final and not block,
            "Итоговые файлы отличаются от выбранного SHA.",
        )
        state.update(
            base=dst, snap=dst, active=False, empty=False, head=dst if head else "", published_branch=branch
        )
        save(sf, state)
        partial.unlink()
        log("published " + dst)
    return result("Выложено и проверено: " + branch + " @ " + dst)


def main(q):
    try:
        print(json.dumps(run(q), ensure_ascii=False))
    except (Stop, OSError, ValueError, RuntimeError, subprocess.TimeoutExpired) as e:
        print(
            json.dumps(
                dict(
                    status="error",
                    message=(
                        str(e)
                        if isinstance(e, Stop)
                        else "Ошибка файловой системы, формата данных или таймаут. Проверьте состояние на хостинге."
                    ),
                ),
                ensure_ascii=False,
            )
        )
        return 1
    return 0
