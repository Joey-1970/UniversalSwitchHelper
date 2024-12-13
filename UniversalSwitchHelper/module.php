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
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
				
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv"); 
		$arrayElements[] = array("type" => "Label", "label" => "Variable die den aktuellen geschaltet werden soll");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "VariableID", "caption" => "Schaltaktor"); 
		$arrayElements[] = array("type" => "Label", "label" => "Wahl des Schaltprogramms:");
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "Manuell", "value" => 1);
		$arrayOptions[] = array("label" => "Zeitgesteuert", "value" => 2);
		$arrayOptions[] = array("label" => "Abhängig", "value" => 3);
		$arrayOptions[] = array("label" => "Timer", "value" => 4);
		
		$arrayElements[] = array("type" => "Select", "name" => "ProgramType", "caption" => "Programm Typ", "options" => $arrayOptions );
 		
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
		$this->SendDebug("Switch", "Schalten mit Wert: ".$Value, 0);
		RequestAction($VariableID, $Value);
	} 
	


	
	    

}
?>
