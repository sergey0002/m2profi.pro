"""Read-only inventory: no fetch, checkout, publication or state creation."""
import base64
import fcntl
import hashlib
import json
import subprocess
from pathlib import Path


def describe(target, repo):
    live = Path(target['path'])
    if not live.is_absolute() or live.resolve() != live or not live.is_dir():
        return {'state': 'unavailable'}
    lock = None
    try:
        state = {}
        if target['guard']:
            key = hashlib.sha256(str(live).encode()).hexdigest()[:24]
            root = Path.home() / '.dep-control' / key
            if (root / 'lock').exists():
                lock = (root / 'lock').open('rb')
                fcntl.flock(lock, fcntl.LOCK_SH | fcntl.LOCK_NB)
            if (root / 'incomplete.json').exists():
                return {'state': 'incomplete'}
            if (root / 'state.json').exists():
                state = json.loads((root / 'state.json').read_text())
                if state.get('repo') != repo:
                    return {'state':'unknown'}
        def git(*args):
            return subprocess.run(['git', '-C', str(live), *args], capture_output=True, timeout=5)
        head = ''
        if (live / '.git').exists():
            top = git('rev-parse','--show-toplevel')
            if top.returncode or Path(top.stdout.decode().strip()).resolve() != live:
                return {'state':'unknown'}
            oid = git('rev-parse','--verify','HEAD^{commit}')
            if oid.returncode:
                # Empty repository: symbolic HEAD can already name an unborn branch.
                ref = git('symbolic-ref','-q','HEAD')
                if ref.returncode == 0 and git('show-ref','--verify','--quiet',ref.stdout.decode().strip()).returncode == 1:
                    return {'state':'none'}
                return {'state':'unknown'}
            head = oid.stdout.decode().strip()
        if state and head != state.get('head',''):
            return {'state':'unknown'}
        if state.get('published_branch'):
            return {'state':'branch','branch':state['published_branch'],'sha':state['base'],'source':'publish'}
        if head:
            ref = git('symbolic-ref','-q','--short','HEAD')
            if not ref.returncode:
                return {'state':'branch','branch':ref.stdout.decode().strip(),'sha':head,'source':'checkout'}
            return {'state':'unknown','sha':head}
        if state and not state.get('empty') and 'published_branch' not in state:
            return {'state':'unknown'}
        return {'state':'none'}
    except BlockingIOError:
        return {'state':'busy'}
    finally:
        if lock:
            lock.close()


def main(q):
    rows = []
    for target in q['targets']:
        try:
            info = describe(target,q['repo'])
        except (OSError,ValueError,RuntimeError,KeyError,subprocess.TimeoutExpired):
            info = {'state':'unavailable'}
        rows.append(dict(name=target['name'],**info))
    print(json.dumps({'status':'ok','targets':rows}))
    return 0
