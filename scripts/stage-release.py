#!/usr/bin/env python3
"""Stage the documented plugin root for Pressship, without development artifacts."""
from pathlib import Path
import shutil

root = Path(__file__).resolve().parent.parent
target = root / 'release' / 'mb-bridge-for-etch'
if target.exists():
    shutil.rmtree(target)
target.mkdir(parents=True)
for name in ('mb-bridge-for-etch.php', 'readme.txt', 'LICENSE'):
    shutil.copy2(root / name, target / name)
for name in ('assets', 'includes'):
    shutil.copytree(root / name, target / name, dirs_exist_ok=True)
print(target)
