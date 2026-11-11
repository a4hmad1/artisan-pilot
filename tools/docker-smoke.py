#!/usr/bin/env python3
"""Exercise the real Docker runner with a disposable, network-isolated container."""
from pathlib import Path
import json
import subprocess
import uuid

root = Path(__file__).resolve().parent.parent
name = 'artisan-shortcuts-qa-' + uuid.uuid4().hex[:10]
image = 'php:8.3-cli-alpine'
started = False
try:
    subprocess.run([
        'docker', 'run', '--detach', '--rm', '--name', name, '--network', 'none',
        '--read-only', '--cap-drop', 'ALL',
        '--volume', str(root / 'tests/Fixtures') + ':/var/www/html:ro',
        '--workdir', '/var/www/html', image, 'sleep', '300',
    ], check=True, capture_output=True, text=True)
    started = True
    command = [
        'php', str(root / 'bin/pa'), '--project', str(root / 'tests/Fixtures'),
        '--runner=docker', '--container=' + name,
    ]
    result = subprocess.run(command + ['probe:args', 'literal space', '$(touch PWNED)'], capture_output=True, text=True)
    assert result.returncode == 0, result.stderr
    assert json.loads(result.stdout) == ['probe:args', 'literal space', '$(touch PWNED)']
    result = subprocess.run(command + ['probe:input'], input='docker stdin\n', capture_output=True, text=True)
    assert result.returncode == 0, result.stderr
    assert result.stdout == 'docker stdin\n'
    assert result.stderr == 'separate stderr\n'
    result = subprocess.run(command + ['probe:exit'], capture_output=True, text=True)
    assert result.returncode == 42, result.stderr
    result = subprocess.run(command + ['shortcuts'], capture_output=True, text=True)
    assert result.returncode == 0, result.stderr
    assert '[AMBIGUOUS]' in result.stdout
    print('Docker smoke passed: discovery, literal arguments, stdin/stdout/stderr, exit 42, collisions.')
finally:
    if started:
        subprocess.run(['docker', 'stop', name], check=True, capture_output=True)
