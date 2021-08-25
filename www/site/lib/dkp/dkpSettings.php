<?php

/*===========================================================

CLASS DESCRIPTION

=============================================================

Class Description should be placed here.

*/



include_once("dkpSetting.php");



class dkpSettings {

	/*===========================================================

	MEMBER VARIABLES

	============================================================*/

	var $guildid;

	var $settings = array();

	var $loaded = false;



	/*===========================================================

	DEFAULT CONSTRUCTOR

	============================================================*/

	function __construct($guildid = "")

	{

		if($guildid != "") {

			$this->guildid = $guildid;

			$this->LoadSettings($guildid);

		}

	}



	function LoadSettings($guildid){

		if($guildid != "")

			$this->guildid = $guildid;



		global $sql;

		$this->settings = array();

		$guildid = sql::Escape($this->guildid);

		$result = $sql->Query("SELECT * FROM dkp_settings

						       WHERE guild='$guildid'");



		while($row = mysqli_fetch_array($result)) {

			$setting = new dkpSetting();

			$setting->loadFromRow($row);

			$this->settings[$setting->name] = $setting;

		}





		$this->loaded = true;

	}



	function IsValueSet($key){

		return (isset($this->settings[$key]));

	}



	function Get($key){

		if(!$this->IsValueSet($key))

			return false;

		return $this->settings[$key]->value;

	}



	function GetDefault($key, $defaultvalue){

		if(!$this->IsValueSet($key))

			$this->Set($key, $defaultvalue);

		return $this->settings[$key]->value;

	}



	function Set($key, $value){

		if($this->IsValueSet($key)) {

			$setting = $this->settings[$key];

			$setting->value = $value;

			$setting->save();

			$this->settings[$key] = $setting;

		}

		else {

			$setting = new dkpSetting();

			$setting->name = $key;

			$setting->value = $value;

			$setting->guild = $this->guildid;

			$setting->saveNew();

			$this->settings[$key] = $setting;

		}

	}



	function LoadDefaultSettings(){

		$this->Set("Proaccount", 0);

		$this->Set("Prostatus", "");

		$this->Set("LootTableEnabled", 0);

		$this->Set("TiersEnabled", 0);

		$this->Set("TierSize", 50);

		$this->Set("ZerosumEnabled", 0);

		$this->Set("LifetimeEnabled", 1);

		$this->Set("CombineAltsEnabled", 0);

		$this->Set("SetsEnabled", 0);

		$this->Set("DisabledSets", array());

	}



	function GetProaccount(){					return ( $this->GetDefault("Proaccount", 0) == 1 );			}

	function GetProstatus(){					return ( $this->GetDefault("Prostatus", ""));				}

	function GetNewProaccount(){				return ( $this->GetDefault("NewProaccount", 0) == 1);		}

	function GetLootTableEnabled(){				return ( $this->GetDefault("LootTableEnabled", 0) == 1 );	}

	function GetTiersEnabled(){					return ( $this->GetDefault("TiersEnabled", 0) == 1 );		}

	function GetTierSize(){						return ( $this->GetDefault("TierSize", 50) );				}

	function GetZerosumEnabled(){				return ( $this->GetDefault("ZerosumEnabled", 0) == 1 );		}

	function GetLifetimeEnabled(){				return ( $this->GetDefault("LifetimeEnabled", 0) == 1 );	}

	function GetCombineAltsEnabled(){			return ( $this->GetDefault("CombineAltsEnabled", 0) == 1 );	}

	function GetSetsEnabled(){					return ( $this->GetDefault("SetsEnabled", 0) == 1 );		}

	function GetDisabledSets(){					return ( $this->GetDefault("DisabledSets", array()) == 1 );	}

	function GetRemoteStyle(){					return ( $this->GetDefault("RemoteStyle", 1));				}



	function SetProaccount($enabled){			$this->Set("Proaccount", $enabled);			}

	function SetProstatus($value){				$this->Set("Prostatus", $value);			}

	function SetNewProaccount($value){			$this->Set("NewProaccount", $value);			}

	function SetLootTableEnabled($enabled){		$this->Set("LootTableEnabled", $enabled);	}

	function SetTiersEnabled($enabled){			$this->Set("TiersEnabled", $enabled);		}

	function SetTierSize($size){				$this->Set("TierSize", $size);				}

	function SetZerosumEnabled($enabled){		$this->Set("ZerosumEnabled", $enabled);		}

	function SetLifetimeEnabled($enabled){		$this->Set("LifetimeEnabled", $enabled);	}

	function SetCombineAltsEnabled($enabled){	$this->Set("CombineAltsEnabled", $enabled);	}

	function SetSetsEnabled($enabled){			$this->Set("SetsEnabled", $enabled);		}

	function SetDisabledSets($sets){			$this->Set("DisabledSets", $sets);			}

	function SetRemoteStyle($styleid){			$this->Set("RemoteStyle", $styleid);		}

}



?>