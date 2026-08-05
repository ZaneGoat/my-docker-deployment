import re

with open('deploy/index.html', 'r') as f:
    content = f.read()

# Remove zoom on mobile to prevent layout breaking
content = re.sub(
    r'@media\(max-width:768px\)\{',
    r'@media(max-width:768px){\n    body{zoom:1 !important; padding:1rem;}',
    content
)

# Make the grid a single column on mobile
content = re.sub(
    r'\.grid\{grid-template-columns:repeat\(auto-fit,minmax\(180px,1fr\)\);gap:0\.6rem;\}',
    r'.grid{grid-template-columns:1fr;gap:0.5rem;}',
    content
)

# Also fix the grid in the media query in case it was modified slightly differently
content = re.sub(
    r'grid-template-columns:repeat\(auto-fit,minmax\(180px,1fr\)\)',
    r'grid-template-columns:1fr',
    content
)

with open('deploy/index.html', 'w') as f:
    f.write(content)

