(function() {
var css = document.createElement('style');
css.textContent = '\
.fab-fixed{\
position:fixed;bottom:2rem;left:2rem;height:50px;padding:0 1.5rem;\
background:rgba(5,5,10,0.96);border:1px solid rgba(0,255,65,0.12);\
color:rgba(0,255,65,0.4);font-size:0.75rem;cursor:pointer;z-index:9999;\
display:flex;align-items:center;gap:0.6rem;transition:all 0.25s;\
font-family:"Fira Code",monospace;letter-spacing:1px;\
}\
.fab-fixed:hover{border-color:rgba(0,255,65,0.25);color:rgba(0,255,65,0.6);background:rgba(0,255,65,0.03);}\
.fab-fixed.open{border-color:rgba(0,255,65,0.2);}\
.fab-fixed .fp{color:rgba(0,255,65,0.2);}\
.fab-fixed .fc{display:inline-block;width:5px;height:0.8em;background:rgba(0,255,65,0.2);animation:blk 1s step-end infinite;}\
@keyframes blk{0%,100%{opacity:1}50%{opacity:0}}\
.fab-menu-fixed{\
position:fixed;bottom:5.8rem;left:2rem;\
display:none;flex-direction:column;gap:2px;\
z-index:9998;min-width:200px;\
}\
.fab-menu-fixed.open{display:flex;}\
.fab-item-fixed{\
background:rgba(5,5,10,0.97);border:1px solid rgba(0,255,65,0.06);\
padding:0.6rem 1.2rem;color:rgba(200,208,224,0.4);\
text-decoration:none;font-size:0.75rem;\
font-family:"Fira Code",monospace;letter-spacing:1px;\
transition:all 0.15s;position:relative;\
animation:fabSlide 0.15s ease-out backwards;\
}\
.fab-item-fixed:hover{border-color:rgba(0,255,65,0.15);color:rgba(0,255,65,0.5);padding-left:1.5rem;}\
.fab-item-fixed .fi-l{flex:1;}\
.fab-item-fixed.home{color:rgba(0,255,65,0.25);border-color:rgba(0,255,65,0.04);}\
.fab-item-fixed.home:hover{color:rgba(0,255,65,0.5);border-color:rgba(0,255,65,0.12);}\
@keyframes fabSlide{from{opacity:0;transform:translateX(10px)}to{opacity:1;transform:translateX(0)}}\
@media(max-width:768px){\
.fab-fixed{bottom:1rem;left:1rem;height:44px;padding:0 1rem;font-size:0.7rem;}\
.fab-menu-fixed{bottom:5rem;left:1rem;min-width:160px;}\
}\
';
document.head.appendChild(css);

var btn = document.createElement('button');
btn.className = 'fab-fixed';
btn.id = 'gfab';
btn.innerHTML = '<span class="fp">$</span><span>nav</span><span class="fc"></span>';

var menu = document.createElement('div');
menu.className = 'fab-menu-fixed';
menu.id = 'gfab-menu';

if (window.location.pathname.indexOf('/ipirnet/') !== -1 && window.location.pathname !== '/ipirnet/login/') { return; }
document.body.appendChild(btn);
document.body.appendChild(menu);

var projects = [
    { l: 'HOME', u: '/' },
    { l: 'Restorent', u: '/ayarestoPHP/' },
    { l: 'ProjrtZL', u: '/projrtZL/' },
    { l: 'Beauty center', u: '/ihsan/' },
    { l: 'Traiteur', u: '/traiteur/' },
    { l: 'Patisserie', u: '/khadija/' },
    { l: 'PizzaTime', u: '/pizzatime/' },
    { l: 'Hotel', u: '/hotel/' },
    { l: 'Field Management', u: '/othman-terrain/' },
    { l: 'KoraArena', u: '/terrain/' },
    { l: 'IPIRNET V7', u: '/ipirnet/' },
];

function build() {
    var items = projects.map(function(p) {
        var cls = p.l === 'HOME' ? 'fab-item-fixed home' : 'fab-item-fixed';
        return '<a href="' + p.u + '" class="' + cls + '"><span class="fi-l">' + p.l + '</span></a>';
    });
    try {
        var saved = localStorage.getItem('customProjects');
        if (saved) {
            JSON.parse(saved).forEach(function(p) {
                items.push('<a href="' + (p.url || '#') + '" class="fab-item-fixed"><span class="fi-l">' + (p.name || '?') + '</span></a>');
            });
        }
    } catch(e) {}
    menu.innerHTML = items.join('');
}

btn.addEventListener('click', function(e) {
    e.stopPropagation();
    btn.classList.toggle('open');
    menu.classList.toggle('open');
    if (menu.classList.contains('open')) build();
});

document.addEventListener('click', function(e) {
    if (!btn.contains(e.target) && !menu.contains(e.target)) {
        btn.classList.remove('open');
        menu.classList.remove('open');
    }
});
})();
