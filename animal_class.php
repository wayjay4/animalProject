<!DOCTYPE HTML>
<html>
<head>
	<title>Simple Animal Class Stucture</title>
</head>

<body>
    <?php
		
	class Animal {
		public $currentAnimal;
		public $animalSelect = array();
		
		// constructor
		public function __construct()
		{
			// set default values
			$this->animalSelect = array(
				"cat"=>"meow", 
				"dog"=>"bark", 
				"duck"=>"quack"
			);
	
			if(isset($_GET["animal"])) :
				$this->currentAnimal = $_SESSION["currentAnimal"] = $_GET["animal"];
			elseif(isset($_SESSION["currentAnimal"])) :
				$this->currentAnimal = $_SESSION["currentAnimal"];
			else :
				$this->currentAnimal = "cat";
			endif;
			
			$this->displayAnimalView();
		} 
		
		// function to display the main view
		public function displayAnimalView()
		{
			// html code here would otherwise be referenced to another document in the ‘view’ folder
			// begin html page
			echo("<h3>Select an animal or click a button to make the animal start walking or make a sound.</h3>");
			echo("<div>".$this->getAnimalSelect()."</div>");
			echo("<div>".$this->getAnimalActionButtons()."</div>");
	
			if($_GET["action"] == "talk") :
				echo $this->makeAnimalTalk();
			elseif($_GET["action"] == "walk") :
				echo $this->makeAnimalWalk();
			endif;
		}
		
		// function create drop down menu to select an animal
		public function getAnimalSelect()
		{
			// local vars
			$items = "";
			$result= "";
			
			// create item list
			foreach($this->animalSelect as $animal=>$sound) :
				$items .= ($animal == $this->currentAnimal) ? 
					"<option selected value=\"$animal\">$animal</option>" :
					"<option value=\"$animal\">$animal</option>";
			endforeach;
			
			$result = "<select onchange=\"window.location='$this->_link?animal='+this[this.selectedIndex].value;return false;\">$items</select>";
			
			return $result;
		}
		
		public function getAnimalActionButtons()
		{
			// local vars
			$sound_button = "";
			$walk_button = "";
			$result = "";
			
			// create action buttons
			$sound_button = "<button value='talk' onclick=\"window.location='$this->_link?action='+this.value;return false;\">Make me talk</button>";
			$walk_button = "<button value='walk' onclick=\"window.location='$this->_link?action='+this.value;return false;\">Make me walk</button>";
			
			$result = $sound_button."&nbsp;".$walk_button;
			
			return $result;
		}
		
		public function makeAnimalTalk()
		{
			// local var
			$result;
			$talking = strtoupper($this->animalSelect[$this->currentAnimal]);
			
			// do something
			$result = "<p style='color:green;'>$talking! $talking! $talking! </p>";
			
			return $result;
		}
		
		public function makeAnimalWalk()
		{
			// local vars
			$result;
			
			// do something
			$result = "<p style='color:blue;'>I am 'WALKING' now!</p>";
			
			return $result;
		}
	}
	
	session_start();
	$myanimal = new Animal();
	
    ?>
</body>
</html>
