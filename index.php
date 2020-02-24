<?php

/**
 * Class ShelfStatus: Enum class to keep shelf status
 */
abstract class ShelfStatus
{
    const _empty = 0;
    const _full = 1;
}

/**
 * Class BoxDoorStatus: Enum class to keep door status
 */
abstract class BoxDoorStatus
{
    const _close = 0;
    const _open = 1;
}

/**
 * Class box: Construct box and handle data and operation
 */
class box {

    //determine how many shelf box will has
    private $shelfCount = 0;

    //determine how many slot a shelf will has
    private $slotCount = 0;

    //keep shelf and slot data as a array
    private $shelfs = array();

    //flag to show log
    private $debug = true;

    //keep door status
    private $doorStatus = BoxDoorStatus::_close;

    /**
     * box constructor.
     * @param int $shelfCount: shelf count of box
     * @param int $slotCount: slot count of a shelf
     * @param bool $debug: show logs if true
     */
    function __construct($shelfCount, $slotCount, $debug = false){
        $this->$debug = $debug;
        $this->shelfCount = $shelfCount;
        $this->slotCount = $slotCount;
    }

    /**
     * initialize box depend on $shelfCount and $slotCount
     */
    public function initBox(){
        if($this->debug){
            echo "box initializing";
            echo "<br>";
        }

        $this->shelfs = array();
        for($i = 0; $i < $this->shelfCount; $i++){
            $slot = [];
            for($j = 0; $j < $this->slotCount; $j++){
                array_push($slot, ShelfStatus::_empty);
            }
            array_push($this->shelfs, $slot);
        }
    }

    /**
     * add item to {$slot} of {$shelf}'s
     *
     * @param int $shelf: desired shelf value to add
     * @param int $slot: desired slot value of to add shelf
     * @return array: latest status of shelfs
     */
    public function addToShelf($shelf, $slot){
        if($this->debug){
            echo "add to $shelf - $slot";
            echo "<br>";
        }

        if($this->doorStatus === BoxDoorStatus::_open){
            $this->shelfs[$shelf][$slot] = ShelfStatus::_full;
        }

        return $this->getShelfs();
    }

    /**
     * remote item from {$slot} of {$shelf}'s
     *
     * @param int $shelf: desired shelf value to remove
     * @param int $slot: desired slot value of to remove from shelf
     * @return array: latest status of shelfs
     */
    public function removeFromShelf($shelf, $slot){
        if($this->debug){
            echo "remove from $shelf - $slot";
            echo "<br>";
        }

        if($this->doorStatus === BoxDoorStatus::_open) {
            $this->shelfs[$shelf][$slot] = ShelfStatus::_empty;
        }

        return $this->getShelfs();
    }

    /**
     * change current door state
     *
     * @param BoxDoorStatus $doorStatus: desired BoxDoorStatus obj
     * to set current $doorStatus
     */
    public function loadDoorStatus($doorStatus){
        $this->doorStatus = $doorStatus;
    }

    /**
     * load shelf data and change shelfs and slots status
     *
     * @param array $shelfData: shelf data to construct box
     */

    public function loadBox($shelfData){
        if($this->debug){
            echo "load box info from session";
            echo "<br>";
        }

        $this->shelfs = $shelfData;
    }

    /**
     * get current shelfs status
     *
     * @return array: current shelfs status
     */
    public function getShelfs(){
        return $this->shelfs;
    }

    /**
     * get current door status
     *
     * @return array: current door status
     */
    public function getDoorStatus(){
        return $this->doorStatus;
    }
}

//USAGE CLASS WITH SESSION

//start session
session_start();

//construct a box with 3 shelf and 20 slot, enable display log.
$box = new box(3, 20 , true);

//if there is no data from previous state, initialize box
//otherwise load previous status of shelfs and door status
if(!isset($_SESSION['shelfs']) && !isset($_SESSION['doorStatus'])) {
    $box->initBox();
} else{
    $box->loadBox($_SESSION['shelfs']);
    $box->loadDoorStatus($_SESSION['doorStatus']);
}

//handle post requests
if(!empty($_POST)){
    switch (key($_POST)){
        case "open":
            echo "Operation: opening box";
            echo "<br>";

            //set doorStatus open
            $_SESSION['doorStatus'] = BoxDoorStatus::_open;

            //update shelfs with latest state
            $_SESSION['shelfs'] = $box->getShelfs();

            break;


        case "close":
            echo "Operation: closing box";
            echo "<br>";

            //set doorStatus close
            $_SESSION['doorStatus'] = BoxDoorStatus::_close;

            //update shelfs with latest state
            $_SESSION['shelfs'] = $box->getShelfs();

            break;

        case "reset":
            echo "Operation: reset box";
            echo "<br>";

            //remove previous shelfs state
            unset($_SESSION['shelfs']);

            //remove previous door state
            unset($_SESSION['doorStatus']);
            
            //init box from beginning
            $box->initBox();

            break;

        case "box":
            //if door not open show message and stop continue
            if($box->getDoorStatus() === BoxDoorStatus::_close){
                echo "<span style='color: red;'>Please open door first</span>";
                break;
            }

            //calculate clicked position from request.
            $shelf = key($_POST["box"]);
            $slot = key($_POST["box"][$shelf]);
            $status = $_POST["box"][$shelf][$slot];

            //update shelf data for next displaying
            if($status == ShelfStatus::_empty){
                $_SESSION['shelfs'] = $box->addToShelf($shelf,$slot);
            }else{
                $_SESSION['shelfs'] = $box->removeFromShelf($shelf,$slot);
            }

            break;
    }
}

?>

<!-- VISUAL TEST -->

<form action="index.php" method="post">

    <br>

    <input type="submit" name="open" value="Open to Box">
    <input type="submit" name="close" value="Close to Box">
    <input type="submit" name="reset" value="Reset to Box">

    <br>
    <br>

    <table>
        <?php
        foreach ($box->getShelfs() as $shelfKey => $shelf){ ?>
            <tr>
                <?php foreach ($shelf as $slotKey => $slot){ ?>
                    <td>
                        <input type="submit" name="box[<?php echo $shelfKey ?>][<?php echo $slotKey ?>]" value="<?php echo $slot ?>">
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
</form>

Session Debug
<hr>
<pre style="width: 200px; height: 100px; float: left">
SHELFS

<?php
    if(!empty($_SESSION['shelfs'])){
        var_dump($_SESSION['shelfs']);
    }
?>
</pre>
<pre style="width: 200px; height: 100px; float: left">
DOOR STATUS

<?php
if(!empty($_SESSION['doorStatus'])){
    var_dump($_SESSION['doorStatus']);
}
?>

</pre>
