(function() {
    var x = document.createElement("script");
    x.type = "text/javascript"; x.async = true;
    x.src = (document.location.protocol === "https:" ? "https://" : "http://") + "libraryh3lp.com/js/libraryh3lp.js?16813";
    var y = document.getElementsByTagName("script")[0];
    y.parentNode.insertBefore(x, y);

    // Override hardcoded iframe widget height
    // Widget isn't always in the DOM by the time this runs.
    // So try a few times before giving up.
    setTimeout(function() {
        var iframe = document.querySelector("iframe");
        iframe.style.height = "200px";
    }, 1500);
})();