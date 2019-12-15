/*=====================================================
Javascript for the welcome page. Provides support
for loading users from the armory into the dkp table
======================================================*/
var Welcome = new (function() {
  this.Init = function() {};

  this.LoadRoster = function() {
    Util.Hide("rosterGood");
    Util.Hide("rosterBad");
    Util.Show("rosterLoad");
    Util.AjaxRequest("LoadRoster", "", Welcome.RosterCallback);
  };

  this.RosterCallback = function(transport) {
    Util.Hide("rosterLoad");
    //$("TimeDiv").innerHTML = transport.responseText;
    var result = transport.responseText.evalJSON();
    if (result[0]) {
      $("rosterGood").innerHTML =
        result[1] + " players found. " + result[2] + " new players added!";
      Util.Show("rosterGood");
    } else {
      Util.Show("rosterBad");
    }
  };
})();
