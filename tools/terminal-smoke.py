#!/usr/bin/env python3
from pathlib import Path
import os
import pty
import select
import signal
import time

root = Path(__file__).resolve().parent.parent
pid, master = pty.fork()
if pid == 0:
    os.execvp('php', ['php', str(root / 'bin/pilot'), '--project', str(root / 'tests/Fixtures'), 'terminal:probe'])
output = b''
finished = False
try:
    deadline = time.monotonic() + 15
    while b'PID=' not in output and time.monotonic() < deadline:
        if select.select([master], [], [], 0.2)[0]:
            output += os.read(master, 8192)
    assert b'TTY=yes' in output, output
    assert ('PID=' + str(pid)).encode() in output, output
    os.write(master, b'\x03')
    while time.monotonic() < deadline:
        found, status = os.waitpid(pid, os.WNOHANG)
        if found:
            finished = True
            assert os.WIFSIGNALED(status) and os.WTERMSIG(status) == signal.SIGINT, status
            break
        time.sleep(0.05)
    assert finished, 'Ctrl-C did not stop the command'
    print('PTY smoke passed: stdin/stdout TTY, process identity, Ctrl-C signal.')
finally:
    os.close(master)
    if not finished:
        os.kill(pid, signal.SIGKILL)
        os.waitpid(pid, 0)
