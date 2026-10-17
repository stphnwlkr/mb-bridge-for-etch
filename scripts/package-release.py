#!/usr/bin/env python3
"""Build an installable plugin ZIP and GitHub release update metadata."""
import argparse
import hashlib
import json
import re
import runpy
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
parser = argparse.ArgumentParser()
parser.add_argument('--repository', required=True, help='https://github.com/owner/repository')
args = parser.parse_args()
repository = args.repository.rstrip('/')
if not re.fullmatch(r'https://github\.com/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+', repository):
    parser.error('Use a public GitHub repository URL without a branch or query string.')
main = (ROOT / 'mb-bridge-for-etch.php').read_text()
readme = (ROOT / 'readme.txt').read_text()
def header(source, name):
    match = re.search(r'^\s*(?:\* )?' + re.escape(name) + r':\s*(.+)$', source, re.M)
    if not match:
        raise ValueError('Missing header: ' + name)
    return match.group(1).strip()
version = header(main, 'Version')
assert version == header(readme, 'Stable tag')
assert header(main, 'Requires at least') == header(readme, 'Requires at least')
assert header(main, 'Requires PHP') == header(readme, 'Requires PHP')
assert header(main, 'Update URI') == repository, 'Update URI must match release repository'
runpy.run_path(str(ROOT / 'scripts/stage-release.py'), run_name='__main__')
stage = ROOT / 'release/mb-bridge-for-etch'
dist = ROOT / 'dist'
dist.mkdir(exist_ok=True)
archive = dist / f'mb-bridge-for-etch-{version}.zip'
with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED) as output:
    for path in sorted(stage.rglob('*')):
        if path.is_file():
            output.write(path, Path('mb-bridge-for-etch') / path.relative_to(stage))
metadata = {
    'name': 'MB Bridge for Etch', 'slug': 'mb-bridge-for-etch', 'version': version,
    'requires': header(main, 'Requires at least'), 'requires_php': header(main, 'Requires PHP'),
    'tested': header(readme, 'Tested up to'), 'homepage': repository,
    'download_url': f'{repository}/releases/download/v{version}/{archive.name}',
    'sections': {'description': header(main, 'Description'), 'changelog': readme.split('== Changelog ==', 1)[1].strip()},
}
(dist / 'update.json').write_text(json.dumps(metadata, indent=2) + '\n')
(dist / 'SHA256SUMS').write_text(''.join(
    hashlib.sha256(path.read_bytes()).hexdigest() + '  ' + path.name + '\n'
    for path in (archive, dist / 'update.json')
))
print(archive)
