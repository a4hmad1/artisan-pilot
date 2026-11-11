#!/usr/bin/env python3
"""Real Sail + Laravel smoke using an isolated Compose service and existing image."""
from pathlib import Path
import json
import os
import subprocess
import sys
import uuid

root = Path(__file__).resolve().parent.parent
fixture = (root / sys.argv[1]).resolve() if len(sys.argv) > 1 else root / 'tests/laravel'
project = 'artisan-pilot-qa-' + uuid.uuid4().hex[:10]
work = root / 'build' / project
work.mkdir(parents=True)
compose = work / 'compose.yaml'
# JSON is valid YAML; use exact strings for paths, avoiding shell/YAML interpolation.
compose.write_text(json.dumps({'services': {'laravel.test': {
    'image': 'php:8.4-fpm-alpine',
    'command': ['sh', '-c', 'adduser -D -u ' + str(os.getuid()) + ' sail && exec sleep 300'],
    'working_dir': str(fixture),
    'volumes': [str(root) + ':' + str(root)],
    'network_mode': 'none',
}}}, indent=2))
env = dict(os.environ, COMPOSE_FILE=str(compose), COMPOSE_PROJECT_NAME=project,
           APP_SERVICE='laravel.test', WWWUSER=str(os.getuid()), WWWGROUP=str(os.getgid()))
command = ['php', str(root / 'bin/pa'), '--project', str(fixture)]

def run(args):
    return subprocess.run(command + args, env=env, cwd=fixture, capture_output=True, text=True, timeout=60)

try:
    result = run(['sail', 'up', '-d'])
    assert result.returncode == 0, result.stdout + result.stderr
    result = run(['--runner=sail', 'commands', '--json'])
    assert result.returncode == 0, result.stdout + result.stderr
    commands = json.loads(result.stdout)
    assert 'pail' in commands and 'sail:install' in commands and 'shortcuts:probe-check' in commands
    result = run(['--runner=sail', 'shortcuts:probe-check', 'sail literal space'])
    assert result.returncode == 0 and 'probe:sail literal space' in result.stdout, result.stdout + result.stderr
    result = run(['--runner=sail', 'shortcuts:probe-check', '--fail'])
    assert result.returncode == 42, result.stdout + result.stderr
    result = run(['--runner=sail', 'pail', '--help'])
    assert result.returncode == 0 and '--filter' in result.stdout, result.stdout + result.stderr
    result = run(['--runner=sail', 'migrate', '--no-interaction'])
    assert result.returncode == 0, result.stdout + result.stderr
    result = run(['--runner=sail', 'patt', '--filter=PilotPassingTest', '--parallel', '--processes=2', '--no-ansi'])
    assert result.returncode == 0, result.stdout + result.stderr
    result = run(['--runner=sail', 'bin', 'phpunit', 'tests/Unit/PilotPassingTest.php'])
    assert result.returncode == 0, result.stdout + result.stderr
    result = run(['--runner=sail', 'papint', '--test', '--preset=psr12', 'tests/Unit/PilotPassingTest.php'])
    assert result.returncode == 0, result.stdout + result.stderr
    container = subprocess.run(['docker', 'compose', 'ps', '-q', 'laravel.test'], env=env, cwd=fixture,
                               capture_output=True, text=True, check=True).stdout.strip()
    docker_args = ['--runner=docker', '--container=' + container, '--container-workdir=' + str(fixture)]
    result = run(docker_args + ['bin', 'phpunit', 'tests/Unit/PilotPassingTest.php'])
    assert result.returncode == 0, result.stdout + result.stderr
    if (fixture / 'vendor/bin/pest').exists():
        result = run(['--runner=sail', 'papest', '--parallel', '--processes=2', '--filter=pilot_pest_case'])
        assert result.returncode == 0, result.stdout + result.stderr
        result = run(docker_args + ['papest', '--filter=pilot_pest_case'])
        assert result.returncode == 0, result.stdout + result.stderr
    print('Real Sail/Docker smoke passed: lifecycle, discovery, Pail, PHPUnit/Pest, parallel tests, Pint, exit codes and SQLite.')
finally:
    subprocess.run(['docker', 'compose', 'down', '--remove-orphans'], env=env, cwd=fixture,
                   capture_output=True, check=True, timeout=60)
