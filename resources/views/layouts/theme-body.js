(function(){
    var m = localStorage.getItem('prescentia-theme-mode') || 'light';
    var c = localStorage.getItem('prescentia-theme-color') || 'blue';
    document.body.dataset.mode  = m;
    document.body.dataset.color = c;
})();
