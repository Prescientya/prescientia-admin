(function(){
    var m = localStorage.getItem('prescentia-theme-mode') || 'light';
    document.body.dataset.mode = m;
    document.body.dataset.color = 'custom';
})();
