var box = null;
document.onmousemove = updateBox;

function updateBox(e) {
  
  if (box != null) {
  
    if (document.all) {
      x = window.event.x + document.body.scrollLeft + 20;
      y = window.event.y + document.body.scrollTop + 20;
      
      h = document.documentElement.clientHeight;
      o = document.documentElement.scrollTop;
    }
    else {
      x = e.pageX + 20;
      y = e.pageY + 20;
      
      h = window.innerHeight;
      o = window.pageYOffset;    
    }
  
    if (box.offsetHeight > h)
      y = o;
    else if (y + box.offsetHeight > h)
      y = o + h - box.offsetHeight;    
  
    box.style.left = x + "px";
    box.style.top  = y + "px";
  }
}

function showBox (en, pid, typ, year, part)
{
  box = document.getElementById(en);
  box.style.display = 'block';  
  
  if ((typ != 'gm') && (typ != 'gs')) {
    var myAjax = new Ajax.Request(
      "inc/upd_box.php",
      {
        method: 'get', 
        parameters: 'pid=' + pid + '&typ=' + typ + '&year=' + year + '&part=' + part,
        onComplete: show_box
      }
    );
  }
  else {
    box.innerHTML = '<img src="inc/graph.php?pid=' + pid + '&typ=' + typ + '&year=' + year + '&part=' + part + '" alt="graph" />';
  }
  
}  
function hideBox (en)
{
  box.style.display = 'none';
  //box.innerHTML = '';
}  

function show_box( originalRequest ) {
   $('hiddenbox').innerHTML = originalRequest.responseText;   
}
