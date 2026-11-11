#!/usr/bin/env python3
"""Exercise bare pas/pamm/pam commands in a disposable real Laravel application."""
from pathlib import Path
import json
import os
import shutil
import signal
import socket
import subprocess
import time
import urllib.error
import urllib.request
import uuid

root = Path(__file__).resolve().parent.parent
original = root / 'tests/laravel'
project = root / 'build' / ('bare-shortcuts-' + uuid.uuid4().hex[:10])
project.mkdir(parents=True)
shutil.copy(original / 'artisan', project / 'artisan')
shutil.copy(original / 'composer.json', project / 'composer.json')
(project / 'bootstrap').mkdir()
shutil.copy(original / 'bootstrap/providers.php', project / 'bootstrap/providers.php')
(project / 'vendor').symlink_to(original / 'vendor', target_is_directory=True)
for directory in ['bootstrap/cache', 'storage/framework/cache', 'storage/framework/views', 'storage/framework/sessions', 'storage/logs', 'public', 'app/Models']:
    (project / directory).mkdir(parents=True, exist_ok=True)
(project / 'public/index.php').write_text('<?php echo "bare-pas-server-ok";')
result = subprocess.run(['php', str(root / 'bin/pa'), 'shell', 'bash'], cwd=project,
                        capture_output=True, text=True, check=True)
activation = result.stdout
script = project / 'commands.sh'
for command in ['pamm BarePost --migration --no-interaction', 'pam --no-interaction']:
    script.write_text(activation + '\n' + command + '\n')
    result = subprocess.run(['bash', '--noprofile', '--norc', str(script)], cwd=project,
                            capture_output=True, text=True, timeout=30)
    assert result.returncode == 0, result.stdout + result.stderr
assert (project / 'app/Models/BarePost.php').is_file()
assert list((project / 'database/migrations').glob('*_create_bare_posts_table.php'))
with socket.socket() as listener:
    listener.bind(('127.0.0.1', 0))
    port = listener.getsockname()[1]
script.write_text(activation + '\npas --host=127.0.0.1 --port=' + str(port) + ' --tries=0 --no-reload\n')
process = subprocess.Popen(['bash', '--noprofile', '--norc', str(script)], cwd=project,
                           stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True,
                           start_new_session=True)
try:
    deadline = time.monotonic() + 15
    response = None
    while time.monotonic() < deadline:
        if process.poll() is not None:
            output, errors = process.communicate()
            raise AssertionError(output + errors)
        try:
            with urllib.request.urlopen('http://127.0.0.1:' + str(port), timeout=1) as handle:
                response = handle.read().decode()
            break
        except (urllib.error.URLError, TimeoutError):
            time.sleep(0.1)
    assert response == 'bare-pas-server-ok', response
    print('Bare shortcut smoke passed: pamm generated a real model/migration, pam migrated SQLite, and pas served HTTP.')
finally:
    try:
        os.killpg(process.pid, signal.SIGTERM)
    except ProcessLookupError:
        pass
    try:
        process.communicate(timeout=5)
    except subprocess.TimeoutExpired:
        os.killpg(process.pid, signal.SIGKILL)
        process.communicate()
