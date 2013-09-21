function HideContent(d) {
if(d.length < 1) { return; }
document.getElementById(d).style.display = "none";
}

function ShowContent(d) {
if(d.length < 1) { return; }
  var el = document.getElementById(d);
    el.style.display = 'block';
}
