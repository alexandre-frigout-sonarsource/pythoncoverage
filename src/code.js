var ATVS_testing = function () {
    const rootDiv = document.getElementById('root');
    const hash = decodeURIComponent(location.hash.substr(1));
    rootDiv.innerHTML = hash;
    function test_a(req, res) {
        const url = req.query.url; // user controlled input
        res.redirect(url); // Noncompliant
    }
    const cp = require('child_process');
    function test_b(req, res) {
        const cmd = 'ls ' + req.query.arg;
        const out = cp.execSync(cmd);
    }
    var iframe = document.getElementById("testiframe");
    iframe.contentWindow.postMessage("secret", "*"); // Noncompliant: * is used
    window.addEventListener("message", function (event) { // Noncompliant: no checks are done on the origin property.
        console.log(event.data);
    });
    //calling functions with dummy params to remove unused function code smells
    test_a('req', 'res');
    test_b('req', 'res');
}
ATVS_testing();