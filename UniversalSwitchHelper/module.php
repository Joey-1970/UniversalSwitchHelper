<?
    // Klassendefinition
    class UniversalSwitchHelper extends IPSModule 
    { 
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
 	    	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("ProgramType", 1);
		$this->RegisterPropertyInteger("VariableID", 1);

		$this->RegisterPropertyInteger("AutoSwitchOff", 0);
		$this->RegisterTimer("AutoSwitchOff", 0, 'UniversalSwitchHelper_AutoSwitchOff($_IPS["TARGET"]);');

		// Anlegen des Wochenplans
		$this->RegisterEvent("Tagesplan", "UniversalSwitchHelper_Event_".$this->InstanceID, 2, $this->InstanceID, 50);
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
				
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv"); 
		$arrayElements[] = array("type" => "Label", "label" => "Variable die geschaltet werden soll");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "VariableID", "caption" => "Schaltaktor"); 
		$arrayElements[] = array("type" => "Label", "label" => "Wahl des Schaltprogramms:");
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "Manuell", "value" => 1);
		$arrayOptions[] = array("label" => "Zeitgesteuert", "value" => 2);
		$arrayOptions[] = array("label" => "Abhängig", "value" => 3);
		$arrayOptions[] = array("label" => "Timer", "value" => 4);
		
		$arrayElements[] = array("type" => "Select", "name" => "ProgramType", "caption" => "Programm Typ", "options" => $arrayOptions );

		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Automatische Ausschaltfunktion"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "AutoSwitchOff", "caption" => "min", "minumum" => 0);
 		
		$arrayActions = array();
		$arrayActions[] = array("type" => "Label", "label" => "Test Center"); 
		$arrayActions[] = array("type" => "TestCenter", "name" => "TestCenter");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		// Profil anlegen
		
	
		// Statusvariablen
		$this->RegisterVariableBoolean("ManuellSwitch", "Manuell", "~Switch", 10);
		$this->EnableAction("ManuellSwitch");

		// Anlegen der Daten für den Wochenplan
		IPS_SetEventScheduleGroup($this->GetIDForIdent("UniversalSwitchHelper_Event_".$this->InstanceID), 0, 127);
		
		//Anlegen von Aktionen 
		IPS_SetEventScheduleAction($this->GetIDForIdent("UniversalSwitchHelper_Event_".$this->InstanceID), 0, "Aus", 0xFF0000, "UniversalSwitchHelper_Automatic(\$_IPS['TARGET'], false);");
		IPS_SetEventScheduleAction($this->GetIDForIdent("UniversalSwitchHelper_Event_".$this->InstanceID), 1, "Ein", 0x0000FF, "UniversalSwitchHelper_Automatic(\$_IPS['TARGET'], true);");

		
		If ($this->HasActiveParent() == true) {	
			If ($this->ReadPropertyBoolean("Open") == true) {
				If ($this->GetStatus() <> 102) {
					$this->SetStatus(102);
				}
			}
			else {
				If ($this->GetStatus() <> 104) {
					$this->SetStatus(104);
				}
			}
		}
	}
	
	public function RequestAction($Ident, $Value) 
	{
		switch($Ident) {
		case "ManuellSwitch":
			$this->Switch($Value);
			
			break;
		
	
		default:
		    throw new Exception("Invalid Ident");
		}
	}
	    
	// Beginn der Funktionen
	public function Switch(Bool $Value)
	{
		If ($this->ReadPropertyBoolean("Open") == false) {
			// Die Instanz ist deaktiviert
			return;
		}
	    	If ($this->ReadPropertyInteger("VariableID") == 0) {
			// Es ist keine Variablen angegeben
			return;
		}
		else {
			$VariableID = $this->ReadPropertyInteger("VariableID");
		}
		$this->SendDebug("Switch", "Schalten mit Wert: ".var_export($Value, true), 0);
		RequestAction($VariableID, $Value);
		$this->SetValue("ManuellSwitch", $Value);

		// Timer setzen
		$AutoSwitchOff = $this->ReadPropertyInteger("AutoSwitchOff");
		$this->SendDebug("Switch", "Ergebnis: ".var_export($Value, true)." AutoSwitchOff: ".$AutoSwitchOff, 0);
		// AutoSwitch
		
		If (($Value == false) AND ($AutoSwitchOff > 0)) {
			$this->SetTimerInterval("AutoSwitchOff", 0);
			$this->SendDebug("AutoSwitchOff", "Timer Reset", 0);
		}
		elseif (($Value == true) AND ($AutoSwitchOff > 0)) {
			$this->SetTimerInterval("AutoSwitchOff", $AutoSwitchOff * 1000 * 60);
			$this->SendDebug("AutoSwitchOff", "Aktiviert", 0);
		}
	} 
	
	public function AutoSwitchOff()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("AutoSwitchOff", "Abschaltung durch AutoSwitchOff", 0);
			$this->Switch(false);
			$this->SetTimerInterval("AutoSwitchoff", 0);
		}
	}

	public function Automatic(bool $State)
	{
		If ($this->ReadPropertyBoolean("Open") == true) {	
			$this->Switch($State);
		}
  	}
	    
	private function RegisterEvent($Name, $Ident, $Typ, $Parent, $Position)
	{
		$eid = @$this->GetIDForIdent($Ident);
		if($eid === false) {
		    	$eid = 0;
		} elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
		    	IPS_DeleteEvent($eid);
		    	$eid = 0;
		}
		//we need to create one
		if ($eid == 0) {
			$EventID = IPS_CreateEvent($Typ);
		    	IPS_SetParent($EventID, $Parent);
		    	IPS_SetIdent($EventID, $Ident);
		    	IPS_SetName($EventID, $Name);
		    	IPS_SetPosition($EventID, $Position);
		    	IPS_SetEventActive($EventID, true);  
		}
	}      

}
?>
