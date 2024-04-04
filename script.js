function openPage(pageName, element) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablink");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(pageName).style.display = "block";
    element.className += " active";

    localStorage.setItem('activeTab', pageName);
  }
  
  // Open the first tab by default
  document.addEventListener("DOMContentLoaded", () => {
    var activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
      openPage(activeTab, document.getElementById(activeTab + 'Tab'));
    } else {
      document.querySelector('.tablink').click();
    }
  });
  