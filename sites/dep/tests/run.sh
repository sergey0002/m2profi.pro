#!/usr/bin/env bash
# Offline regression suite. The SSH shim refuses every remote command except bash -s.
set -euo pipefail
root=$(cd "$(dirname "$0")/.." && pwd); t=$(mktemp -d);trap 'rm -rf -- "$t"' EXIT
export HOME=$t/home DEP_LOG_DIR=$t/logs PATH="$t/bin:$PATH" GIT_CONFIG_NOSYSTEM=1 GIT_TERMINAL_PROMPT=0
export T=$t ROOT=$root; mkdir -p "$HOME" "$t/bin" "$t/site" "$t/panel/projects";cp "$root/index.php" "$root/ajax.php" "$root/deploy_remote.sh" "$root/get_branches.sh" "$t/panel/";cp -r "$root/control" "$t/panel/"
cat > "$t/bin/ssh" <<'EOF'
#!/bin/bash
[[ ${!#} == 'bash -s' ]] || { echo 'unexpected remote command' >&2;exit 127; }
if [[ ${REQUIRE_PASSWORD:-} == 1 && ${FIXTURE_AUTH:-} != ok ]];then echo 'Permission denied (publickey,password).' >&2;exit 255;fi
if [[ ${FAIL_SSH:-} == 1 ]];then printf 'Actual diagnostic: Host key verification failed\n' >&2;exit 255;fi
# Deliberate split across writes, including a multiline secret.
printf 'ordinary stderr before test\nsecret fixture: abcd' >&2;printf 'efgh234\n' >&2
printf 'Authorization: Bearer never-show-this\n-----BEGIN PRIVATE KEY-----\nnever-show-key\n-----END PRIVATE KEY-----\n' >&2
if [[ ${LEGACY_FIXTURE:-} == 1 ]];then
    printf 'regular legacy stdout\n'; printf 'regular legacy stderr\n' >&2
    printf '%0900d:long-line-end\n' 0
    printf 'known secret abc';sleep .08;printf 'defgh234\n';exit 7
fi
exec bash -s
EOF
chmod +x "$t/bin/ssh";git config --global user.name tester;git config --global user.email test@example.org
git init -q --bare "$t/remote";git init -qb test "$t/dev";printf 'first\n' > "$t/dev/index.txt";git -C "$t/dev" add .;git -C "$t/dev" commit -qm initial;git -C "$t/dev" push -q "$t/remote" test
url=https://github.com/example/fixture.git;git config --global url."$t/remote".insteadOf "$url"
cat > "$t/panel/projects/demo.env" <<EOF
DIRS=pilot:$t/site|test,legacy:$t/missing|test,live:$t/prod|prod
ALLOWED_USERS=operator
FTP_CONTROL_TARGETS=pilot
SSH_USER=fixture
SSH_HOST=fixture.invalid
SSH_PASS=abcdefgh234
REPO_URL=$url
FTP_WRITE_BRANCHES=test,test/*
FTP_NEW_FILES=src/*
EOF
php "$root/tests/suite.php"
# The journal stays beside index.php even when open_basedir allows ONLY public_html.
web=$t/domain/public_html; mkdir -p "$web/sites/dep/control" "$web/sites/dep/projects"
cp "$root/control/operations.php" "$web/sites/dep/control/"
out=$(php -d display_errors=1 -d open_basedir="$web" -r '
    $_SERVER["DOCUMENT_ROOT"]=$argv[1]; require $argv[1]."/sites/dep/control/operations.php";
    opsInit("fixture");opsWrite("fixture","ok");opsWrite("fixture","ok");
' "$web" 2>&1)
log=$web/sites/dep/operations.log.php
[[ -z $out && $(wc -l < "$log") == 3 && -z $(php "$log") && ! -e $t/domain/private ]]
printf 'OK: guarded append-only log beside panel, no direct PHP output, no open_basedir warnings\n'
