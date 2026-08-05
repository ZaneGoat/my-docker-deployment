import re

with open('deploy/index.html', 'r') as f:
    content = f.read()

# 1. Readability
content = re.sub(r'color:#d0d8e8;', r'color:#ffffff;', content)
content = re.sub(r'font-weight:600;color:#e0e8f0;', r'font-weight:700;color:#ffffff;', content)
content = re.sub(r'box-shadow:0 0 25px rgba\(0,255,65,0\.05\),inset 0 0 25px rgba\(0,255,65,0\.02\);', r'box-shadow:0 0 15px rgba(0,255,65,0.1);', content)

# 2. Compact list & hide extra stuff
content = re.sub(r'\.card\{([^}]+)\}', r'.card{\1 display:flex; justify-content:space-between; align-items:center; padding:0.8rem 1.2rem; min-height:50px;}', content)
content = re.sub(r'\.card p\{([^}]+)\}', r'.card p{display:none;}', content)
content = re.sub(r'\.card \.tag\{([^}]+)\}', r'.card .tag{display:none;}', content)
content = re.sub(r'\.card \.data-dots\{([^}]+)\}', r'.card .data-dots{display:none;}', content)
content = re.sub(r'\.card \.arrow\{([^}]+)\}', r'.card .arrow{position:static; color:rgba(0,255,65,0.5); font-size:1.2rem; transition:all 0.3s; font-family:"Fira Code",monospace;}', content)
content = re.sub(r'\.card h2\{([^}]+)\}', r'.card h2{font-size:1.1rem; font-weight:700; color:#ffffff; margin-bottom:0; position:relative; z-index:1; font-family:"Fira Code",monospace;}', content)

# 3. Hierarchy & spacing
content = re.sub(r'\.sig-wrapper img\{([^}]+)\}', r'.sig-wrapper img{max-height:160px; display:block; filter:drop-shadow(0 0 15px rgba(0,255,65,0.4)); transition:filter 0.5s,transform 0.5s;}', content)
content = re.sub(r'\.sig-wrapper\{([^}]+)\}', r'.sig-wrapper{\1 margin-bottom:1.5rem;}', content)
content = re.sub(r'\.sig-line\{([^}]+)\}', r'.sig-line{display:none;}', content)

# 4. Remove heavy decorative effects
content = re.sub(r'\.particles\{([^}]+)\}', r'.particles{display:none;}', content)
content = re.sub(r'\.star\{([^}]+)\}', r'.star{display:none;}', content)
content = re.sub(r'body::after\{([^}]+)rgba\(0,0,0,0\.12\)([^}]+)\}', r'body::after{\1rgba(0,0,0,0.03)\2}', content)
content = re.sub(r'animation:gridPulse 8s ease-in-out infinite alternate;', r'', content)

# 5. Hide status panel & boot text
content = re.sub(r'\.status-bar\{([^}]+)\}', r'.status-bar{display:none;}', content)
content = re.sub(r'\.boot-line\{([^}]+)\}', r'.boot-line{display:none;}', content)

# 6. CTA Button
content = re.sub(r'\.add-btn\{([^}]+)\}', r'.add-btn{margin-top:2rem; padding:0.8rem 2rem; background:#00ff41; color:#05050a; font-family:"Fira Code",monospace; font-size:0.9rem; font-weight:700; letter-spacing:2px; cursor:pointer; border:none; transition:all 0.25s; text-transform:uppercase; border-radius:4px; box-shadow:0 0 15px rgba(0,255,65,0.3);}', content)
content = re.sub(r'\.add-btn:hover\{([^}]+)\}', r'.add-btn:hover{background:#00cc33; transform:scale(1.05); box-shadow:0 0 25px rgba(0,255,65,0.5);}', content)
content = re.sub(r'\.add-btn::before\{([^}]+)\}', r'.add-btn::before{display:none;}', content)

# 7. Grid
content = re.sub(r'\.grid\{([^}]+)grid-template-columns:repeat\(auto-fit,minmax\(280px,1fr\)\)([^}]+)\}', r'.grid{\1grid-template-columns:repeat(auto-fit,minmax(300px,1fr))\2}', content)

with open('deploy/index.html', 'w') as f:
    f.write(content)

