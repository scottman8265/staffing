<?php

session_start();

require_once '../class/Process.php';

$empID      = isset($_POST['empID']) ? $_POST['empID'] : null;
$tblID      = isset($_POST['tblID']) ? $_POST['tblID'] : null;
$tmName     = isset($_POST['tmName']) ? $_POST['tmName'] : null;
$position   = isset($_POST['position']) ? $_POST['position'] : null;
$branchNum  = isset($_POST['branchNum']) ? $_POST['branchNum'] : null;
$branchName = isset($_POST['branchName']) ? $_POST['branchName'] : null;
$axsLevel   = isset($_SESSION['axsLevel']) ? $_SESSION['axsLevel'] : null;

$positionTitle = !is_null($branchNum) && !is_null($branchName) && !is_null($position)
	? "Comments for " . $position . " @ " . $branchNum . " - " . $branchName
	: "Invalid Data";

$empTitle = !is_null($empID) && !is_null($tmName) ? "Comments for " . $tmName : "Invalid Data";

function getComments($axsLevel, $tblID, $empID) {

	$lnk    = new Process();
	$comArr = ['pos' => [],
	           'emp' => []
	];

	$newSql = "SELECT * FROM staffing.tmComments WHERE comClass = 'new' && comActive = 1 && comEmpId = '" . $empID . "' && comTblId = " . $tblID;
	$axsLevel === 'branch' ? $newSql .= " && execOnly = false" : null;
	$newSql .= " ORDER BY comTime DESC";
	$newQry = $lnk->query($newSql);

	$replySql = "SELECT * FROM staffing.tmComments WHERE comClass = 'reply' && comActive = 1 && comEmpId = '" . $empID . "' && comTblId = " . $tblID;
	$axsLevel === 'branch' ? $replySql .= " && execOnly = false" : null;
	$replySql .= " ORDER BY comTime ASC";
	$replyQry = $lnk->query($replySql);
	
	if ($newQry) {
		foreach ($newQry as $k => $v) {
			$time     = new DateTime($v['comTime']);
			$comDate  = $time->format('m/d/yy');
			$comClass = $v['comClass'] === 'edit' ? 'new' : $v['comClass'];
			$comArr[$v['comType']][$v['comID']]['new'][] = [
				'comByName' => $v['comByName'], #who wrote the comment/reply
				'comment'   => $v['comment'],   #actual comment
				'comTime'   => $comDate,        #date comment was written ordered by time
				'comEmpId'  => $v['comEmpId'],  #employee id at time of comment
				'comTblId'  => $v['comTblId'],  #staffing table id
				'comType'   => $v['comType'],   #can only be pos or emp
				'comLevel'  => $v['comLevel'],  #can only be exec or branch
				'comClass'  => $comClass,  #can only be new, reply or edit
				'comID'     => $v['comID'],     #id of comment for replies & edits
				'comOrigID' => $v['comOrigID'], #original id for replies
				'execOnly'  => $v['execOnly']   #can only be true or false
			];
		}
	}

	if ($replyQry) {
		foreach ($replyQry as $x => $y) {
			$time     = new DateTime($y['comTime']);
			$comDate  = $time->format('m/d/yy');
			$comClass = $y['comClass'] === 'edit' ? 'new' : $y['comClass'];
			$comArr[$y['comType']][$y['comOrigID']]['reply'][] = [
				'comByName' => $y['comByName'], #who wrote the comment/reply
				'comment'   => $y['comment'],   #actual comment
				'comTime'   => $comDate,        #date comment was written ordered by time
				'comEmpId'  => $y['comEmpId'],  #employee id at time of comment
				'comTblId'  => $y['comTblId'],  #staffing table id
				'comType'   => $y['comType'],   #can only be pos or emp
				'comLevel'  => $y['comLevel'],  #can only be exec or branch
				'comClass'  => $comClass,  #can only be new, reply or edit
				'comID'     => $y['comID'],     #id of comment for replies & edits
				'comOrigID' => $y['comOrigID'], #original id for replies
				'execOnly'  => $y['execOnly']   #can only be true or false
			];
		}
	}


	return $comArr;
}

$comments = getComments($axsLevel, $tblID, $empID);
#var_dump($comments['pos']);
#var_dump($comments['pos']['59']);

?>
<div class="dialogCommentWrapper">
    <div data-tblid="<?php echo $tblID ?>" data-empID="<?php echo $empID ?>"
         class='addComment container-contact1-form-btn'>
        <button class='addCommentBtn contact1-form-btn' onclick="newComment(this)">
            <span id='addCommentText'>Add Comment<i class='fa fa-long-arrow-right' aria-hidden='true'></i></span>
        </button>
    </div>
    <div class="tab">
        <button data-title="<?php echo $positionTitle ?>" class="posLink tabLinks active"
                onClick="commentTabs('Position', this)">Position (<span
                    class="posCount"><?php echo count($comments['pos']) ?></span>)
        </button>
        <button data-title="<?php echo $empTitle ?>" class="tmLink tabLinks"
                onClick="commentTabs('teamMember', this)">Team Member (<span
                    class="empCount"><?php echo count($comments['emp']) ?></span>)
        </button>
    </div>
    <div class="commentWrapper">
        <div class="commentContainer">
            <div class="comments">
				<?php
				if ($comments) { ?>
					<?php
					foreach ($comments as $type => $commentIDs) {
						if ($type === 'pos' && !empty($comments['pos'])) {
							foreach ($commentIDs as $commentID => $commentClasses) {
							    #var_dump($commentClasses);
								foreach ($commentClasses as $commentClass => $keys) {
									foreach ($keys as $key => $item) {
										?>
                                        <div data-remove="<?php echo $item['comID'] ?>"
                                             class="Position tabContent active comWrapper <?php if ($item['execOnly']) {
											     echo 'comExecOnly';
										     }
										     else {
											     echo 'noExecOnly';
										     }
										     if ($commentClass === 'new') {
											     echo ' new';
										     }
										     else {
											     echo ' reply';
										     } ?>">
                                            <div class="comHeader">
                                                <div class="comBy">
                                                    <p>Posted By: <?php echo $item['comByName'] ?>
                                                        @ <?php echo $item['comTime'] ?></p>
                                                </div>
                                                <div class="comType">
                                                    <p>Class: <?php echo $item['comLevel'] ?></p>
                                                </div>
                                            </div>
                                            <div class="comment">
                                                <p><?php echo $item['comment'] ?></p>
                                            </div>
                                            <div data-class="pos" data-comid="<?php echo $item['comID'] ?>"
                                                 data-tblid="<?php echo $item['comTblId'] ?>"
                                                 data-empid="<?php echo $item['comEmpId'] ?>"
                                                 data-origcomid="<?php echo $item['comOrigID'] ?>"
                                                 data-execonly="<?php echo $item['execOnly']?>"
                                                 class="comFooter">
                                                <button data-class="edit"
                                                        class='maniComBtn editCom ui-button ui-corner-all'
                                                        onclick="maniCom(this)">Edit
                                                </button>
                                                <button data-class="reply"
                                                        class='maniComBtn replyCom ui-button ui-corner-all'
                                                        onclick="maniCom(this)">Reply
                                                </button>
                                                <button data-class="del"
                                                        class='maniComBtn deleteCom ui-button ui-corner-all'
                                                        onclick="maniCom(this)">Delete
                                                </button>
                                            </div>
                                        </div>
										<?php
									}
								}
							}
						}
                        elseif ($type === 'pos' && empty($comments['pos'])) { ?>
                            <div class="Position tabContent active comWrapper">
                                <div class="comWrapper"><h1>There Are No Comments</h1></div>
                            </div>
							<?php
						}
                        elseif ($type = 'emp' && !empty($comments['emp'])) {
						    sort($commentIDs);
							foreach ($commentIDs as $commentID => $commentClasses) {
								foreach ($commentClasses as $commentClass => $keys) {
									foreach ($keys as $key => $item) {
										?>
                                        <div data-remove="<?php echo $commentID ?>"
                                             class="teamMember tabContent comWrapper <?php if ($item['execOnly']) {
											     echo 'comExecOnly';
										     }
										     else {
											     echo 'noExecOnly';
										     }
                                             if ($commentClass === 'new') {
	                                             echo ' new';
                                             }
                                             else {
	                                             echo ' reply';
                                             }?>">
                                            <div class="comHeader">
                                                <div class="comBy">
                                                    <p>Posted By: <?php echo $item['comByName'] ?>
                                                        @ <?php echo $item['comTime'] ?></p>
                                                </div>
                                                <div class="comType">
                                                    <p>Class: <?php echo $item['comLevel'] ?></p>
                                                </div>
                                            </div>
                                            <div class="comment">
                                                <p><?php echo $item['comment'] ?></p>
                                            </div>
                                            <div data-class="emp" data-comid="<?php echo $item['comID'] ?>"
                                                 data-tblid="<?php echo $item['comTblId'] ?>"
                                                 data-empid="<?php echo $item['comEmpId'] ?>"
                                                 data-origcomid="<?php echo $item['comOrigID'] ?>"
                                                 data-execonly="<?php echo $item['execOnly']?>"
                                                 class="comFooter">
                                                <button data-class="edit"
                                                        class='maniComBtn editCom ui-button ui-corner-all'
                                                        onclick="maniCom(this)">Edit
                                                </button>
                                                <button data-class="reply"
                                                        class='maniComBtn replyCom ui-button ui-corner-all'
                                                        onclick="maniCom(this)">Reply
                                                </button>
                                                <button data-class="del"
                                                        class='maniComBtn deleteCom ui-button ui-corner-all'
                                                        onclick="maniCom(this)">Delete
                                                </button>
                                            </div>
                                        </div>
										<?php
									}
								}
							}
						}
                        elseif ($type = 'emp' && empty($comments['emp'])) { ?>
                            <div class="teamMember tabContent comWrapper">
                                <div class="comWrapper"><h1>There Are No Comments</h1></div>
                            </div>
							<?php
						}
					}

				}
				else {
					echo '<div class="Position tabContent active comWrapper">
                        <div class="comWrapper"><h1>There Are No Comments</h1></div>
                    </div>
                    <div class="teamMember tabContent comWrapper">
                        <div class="comWrapper"><h1>There Are No Comments</h1></div>
                    </div>';
				} ?>
            </div>
        </div>
    </div>
</div>

                                                                                                                                                                                                                                                                                           