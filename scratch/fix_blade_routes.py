import re
import os

files_to_fix = [
    'resources/views/layouts/gel-secretary.blade.php',
    'resources/views/layouts/gel-accountant.blade.php',
    'resources/views/layouts/gel-direction.blade.php'
]

for file_path in files_to_fix:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern for {{ route('...') }} or {{ route("...") }} anywhere
    # This handles both single and double quotes.
    pattern_bare = re.compile(r'\{\{\s*route\(\s*[\'"]([^\'"]+)[\'"][^\}]*\}\}')
    
    # We will just replace ALL occurrences of {{ route(...) }} with '#'
    # Except 'logout' and 'dashboard' which we already fixed, but wait, those were already replaced.
    # Oh wait, earlier we replaced href="#" data-route="logout" back to action="{{ route('logout') }}".
    # So we don't want to break logout or dashboard!
    
    def replacement(match):
        route_name = match.group(1)
        if route_name in ['logout', 'dashboard']:
            return match.group(0) # Keep it unchanged
        return "'#'"

    new_content = pattern_bare.sub(replacement, content)

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)

print("Done fixing routes with double quotes in blade files.")
