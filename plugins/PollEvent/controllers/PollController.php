<?php


class PollController extends Gdn_Controller {
    /**
     * Used to submit a poll vote via form
     * @param VanillaController $Sender DiscussionController
     */
    public function Submit() {
        $Session = Gdn::Session();
        $FormPostValues = Gdn::request()->post();

        // not submitting anything
        if(Gdn::request()->isAuthenticatedPostback() === FALSE || !GetValue('DiscussionID', $FormPostValues)) {
            // throw permission exception
            throw PermissionException();
        }
        else {
            // You have to have voting privilege only
            if(!$Session->CheckPermission('Plugins.DiscussionPolls.Vote', FALSE) || !$Session->UserID) {
                Gdn::Session()->Stash('DiscussionPollsMessage', T('Plugins.DiscussionPolls.UnableToSubmit', 'You do not have permission to submit a poll.'));
                Redirect('discussion/' . $FormPostValues['DiscussionID']);
            }

            $DPModel = new PollModel();

            if(!$DPModel->CheckFullyAnswered($FormPostValues)) {
                //save partial answers
                $Partial = $DPModel->SavePartialAnswer($FormPostValues, $Session->UserID);
            }
            else {
                $Saved = $DPModel->SaveAnswer($FormPostValues, $Session->UserID);
            }

            // Return the proper view
            if($this->DeliveryType() == DELIVERY_TYPE_VIEW) {
                // Used for AJAX poll submission returns the results
                $Poll = $DPModel->GetByDiscussionID($FormPostValues['DiscussionID']);
                if($Saved) {
                    $ResultsModule = new ResultsModule();
                    $ResultsModule->setData('Poll', $Poll);
                    $Results = $ResultsModule->toString();
                    $Type = 'Full Poll';
                }
                else {
                    $Results = T('Plugins.DiscussionPolls.SavedPartial', 'We have saved your completed poll questions.');
                    $Type = 'Partial Poll';
                }
                $Data = array('html' => $Results, 'type' => $Type);
                echo json_encode($Data);
            } else {
                if($Saved) {
                    // Don't stash any message
                }
                else if($Partial) {
                    Gdn::Session()->Stash('DiscussionPollsMessage', T('Plugins.DiscussionPolls.UnsweredAllQuestions', 'You have not answered all questions!'));
                }
                else {
                    Gdn::Session()->Stash('DiscussionPollsMessage', T('Plugins.DiscussionPolls.UnsweredUnable', 'Unable to save!'));
                }

                Redirect('discussion/' . $FormPostValues['DiscussionID']);
            }
        }
    }

    /**
     * Renders the results of a poll either full page for legacy users
     * or as a partial for frontend ajax
     * @param $PollID Poll to get results for
     */
    public function Results($PollID) {
        $Sender = $this;
        $DPModel = new PollModel();
        $Poll = $DPModel->Get($PollID);
        $ResultsModule = new ResultsModule();
        $ResultsModule->setData('Poll', $Poll);
        $ResultsModule = $ResultsModule->toString();
        if($Sender->DeliveryType() == DELIVERY_TYPE_VIEW) {
            $Data = array('html' => $ResultsModule);
            echo json_encode($Data);
        }
        else {
            $PollModule = new PollModule();
            $PollModule->setData('PollString', $ResultsModule);
            echo $PollModule->ToString();
        }
    }

    /**
     * Remove vote from poll
     * @param $PollID
     */
    public function Devote($PollID){
        $Session = Gdn::session();
        $UserID = $Session->UserID;
        $DPModel = new PollModel();
        $DPModel->DeVote($UserID, $PollID);
        $Poll = $DPModel->Get($PollID);
        PollEventPlugin::log_er($Poll);
        if($this->deliveryType() == DELIVERY_TYPE_VIEW) {
            $this->PollForm = new Gdn_Form();
            $this->PollForm->AddHidden('DiscussionID', $Poll->DiscussionID);
            $this->PollForm->AddHidden('PollID', $Poll->PollID);
            $VotingModule = new VotingModule();
            $VotingModule->setData('PollForm', $this->PollForm);
            $VotingModule->setData('Poll', $Poll);

            $Results = $VotingModule->toString();
            $Type = 'Full Poll';
            $Data = array('html' => $Results, 'type' => $Type);
            echo json_encode($Data);
        }

    }

    public function Delete($PollID) {
        $Session = Gdn::Session();
        $DPModel = new PollModel();
        $DiscussionModel = new DiscussionModel();

        $Poll = $DPModel->Get($PollID);

        $Discussion = $DiscussionModel->GetID($Poll->DiscussionID);

        $PollOwnerID = $Discussion->InsertUserID;

        if($Session->CheckPermission('Plugins.DiscussionPolls.Manage') || $PollOwnerID == $Session->UserID) {
            $DPModel = new PollModel();
            $DPModel->Delete($PollID);

            $Result = 'Removed poll with id ' . $PollID;
            if($this->deliveryType() == DELIVERY_TYPE_VIEW) {
                $Data = array('html' => $Result);
                echo json_encode($Data);
            }
            else {
                $this->SetData('PollString', $Result);
                $this->Render($this->fetchView('poll', '' , PollEventPlugin::$ApplicationFolder));
            }
        }
        else {
            // throw permission exception
            throw PermissionException();
        }
    }
}
