#!/usr/bin/env python3
"""
Extract Eden-controller and Eden-Vue files from a Claude Code transcript JSONL.
Reconstructs files from sequential Write/Edit operations.
"""
import json
import sys
import os
from collections import defaultdict

TRANSCRIPT = r"C:\Users\Gaming Ghislain\.claude\projects\c--xampp-htdocs-Para\88b2f18f-1fe6-4b17-bb04-c332f5ce18cb.jsonl"

# Files to restore
TARGET_FILES = [
    "app/Http/Controllers/Eden/ClientController.php",
    "resources/js/Pages/Eden/Clients/Index.vue",
    "resources/js/Layouts/EdenLayout.vue",
]

def parse_jsonl(path):
    """Parse each line of the JSONL file. Returns list of parsed JSON objects."""
    entries = []
    with open(path, 'r', encoding='utf-8') as f:
        for i, line in enumerate(f, 1):
            line = line.strip()
            if not line:
                continue
            try:
                entries.append(json.loads(line))
            except json.JSONDecodeError as e:
                print(f"Error parsing line {i}: {e}", file=sys.stderr)
    return entries

def find_operations(entries):
    """
    Walk through messages and extract Write/Edit operations that touch target files.
    Returns dict: file_path -> list of operations in chronological order.
    Each operation: {type: 'write'|'edit', content: str|dict, line_no: int}
    """
    operations = defaultdict(list)

    for entry in entries:
        # Handle both 'messages' array format and individual message format
        if 'messages' in entry:
            messages = entry['messages']
        else:
            messages = [entry]

        for msg in messages:
            if not isinstance(msg, dict):
                continue
            role = msg.get('role', '')
            content = msg.get('content', '')

            if role != 'assistant':
                continue

            # content can be a string or an array of content blocks
            if isinstance(content, str):
                # Try to parse tool use from text
                continue

            if not isinstance(content, list):
                continue

            for block in content:
                if not isinstance(block, dict):
                    continue

                if block.get('type') == 'tool_use':
                    tool_name = block.get('name', '')
                    inp = block.get('input', {})

                    if tool_name == 'Write':
                        fp = inp.get('file_path', '')
                        # Check if it matches our target or is Eden-related
                        if any(t in fp for t in ['Eden', 'eden']):
                            operations[fp].append({
                                'type': 'write',
                                'content': inp.get('content', ''),
                                'input': inp
                            })

                    elif tool_name == 'Edit':
                        fp = inp.get('file_path', '')
                        if any(t in fp for t in ['Eden', 'eden']):
                            operations[fp].append({
                                'type': 'edit',
                                'content': {
                                    'old_string': inp.get('old_string', ''),
                                    'new_string': inp.get('new_string', ''),
                                },
                                'input': inp
                            })

    return operations

def apply_edit(content, old_string, new_string):
    """Apply a single edit to content. Returns modified content."""
    if old_string not in content:
        print(f"  WARNING: Could not find old_string in content!")
        print(f"    old_string length: {len(old_string)}")
        print(f"    content length: {len(content)}")
        # Try to find a partial match for debugging
        idx = content.find(old_string[:50])
        if idx >= 0:
            print(f"    Found partial match at position {idx}")
        return content  # Skip this edit
    return content.replace(old_string, new_string, 1)

def reconstruct_file(ops):
    """
    Reconstruct a file from a sequence of Write/Edit operations.
    A Write operation sets the base content, then Edits modify it.
    """
    content = None

    for i, op in enumerate(ops):
        if op['type'] == 'write':
            content = op['content']
            print(f"  Write operation #{i+1}: {len(content)} chars")
        elif op['type'] == 'edit':
            if content is None:
                print(f"  ERROR: Edit #{i+1} found before any Write!")
                continue
            old = op['content']['old_string']
            new = op['content']['new_string']
            prev_len = len(content)
            content = apply_edit(content, old, new)
            new_len = len(content)
            print(f"  Edit #{i+1}: {prev_len} -> {new_len} chars")

    return content

def main():
    print(f"Parsing transcript: {TRANSCRIPT}")
    entries = parse_jsonl(TRANSCRIPT)
    print(f"Found {len(entries)} entries")

    ops = find_operations(entries)

    if not ops:
        print("No Eden-related operations found!")
        return

    print(f"\nFound operations for {len(ops)} files:")
    for fp, file_ops in sorted(ops.items()):
        print(f"\n  {fp}: {len(file_ops)} operations")
        for i, op in enumerate(file_ops):
            t = op['type']
            if t == 'write':
                print(f"    [{i+1}] Write ({len(op['content'])} chars)")
            elif t == 'edit':
                olen = len(op['content']['old_string'])
                nlen = len(op['content']['new_string'])
                print(f"    [{i+1}] Edit (old={olen}, new={nlen})")

    # Reconstruct each file
    print("\n" + "="*60)
    print("RECONSTRUCTION")
    print("="*60)
    for fp, file_ops in sorted(ops.items()):
        print(f"\n--- Reconstructing: {fp} ---")
        content = reconstruct_file(file_ops)

        if content is None:
            print(f"  FAILED: No content generated for {fp}")
            continue

        # Determine output path
        if fp.startswith("/"):
            out_path = fp  # Absolute path
        else:
            out_path = os.path.join("C:\\xampp\\htdocs\\Para", fp.replace("/", "\\"))

        # Create directory
        out_dir = os.path.dirname(out_path)
        os.makedirs(out_dir, exist_ok=True)

        with open(out_path, 'w', encoding='utf-8') as f:
            f.write(content)

        print(f"  WROTE {len(content)} bytes -> {out_path}")

if __name__ == '__main__':
    main()
