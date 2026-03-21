(function(){
    var m = localStorage.getItem('prescentia-theme-mode') || 'light';
    var c = localStorage.getItem('prescentia-theme-color') || 'blue';
    document.documentElement.setAttribute('data-pre-mode', m);
    document.documentElement.setAttribute('data-pre-color', c);
})();
