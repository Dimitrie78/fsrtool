<!--

var zeilen;
function init () {
  zeilen = document.getElementsByTagName('tr');
  reihen = document.getElementsByTagName('td');
}

function einaus (was) {
  for (i=0; i<zeilen.length ; i++ ) {
    if(zeilen[i].id.indexOf(was)>=0) {
      if(zeilen[i].style.display=='none') {
        if(document.all&&!window.opera) {
           zeilen[i].style.display='block';
        } else {
           zeilen[i].style.display='table-row';
        }
      } else {
        zeilen[i].style.display='none';
      }
    }
  }
}

function aufzu (was) {
  reihen = document.getElementsByTagName('td');
  for (i=0; i<reihen.length ; i++ ) {
    if(reihen[i].id.indexOf(was)>=0) {
      if(reihen[i].style.display=='none') {
        if(document.all&&!window.opera) {
           reihen[i].style.display='block';
        } else {
           reihen[i].style.display='table-cell';
        }
      }
    } else { 
	  if(reihen[i].style.display=='table-cell') {
        reihen[i].style.display='none';
	  }
	  if(reihen[i].style.display=='block') {
        reihen[i].style.display='none';
	  }
    }
  }
}
//-->
