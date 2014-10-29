<?php

class Controller_UserUpvote extends Controller_Abstract
{
    function __construct() {
        ToroHook::add("after_handler", function($params) {
                      // TODO : Not the best approach to get the elected User ID. Works for now.
                      $userId = $params['regex_matches'];
                      $votingUser = $this->_getContainer()->User()->loadByUsername($this->_getUsername());
                      $electedUser = $this->_getContainer()->User()->load($userId); 
                      $twit = new Services_TwitterNotify(new Model_LocalConfig);
                      $twit->send($electedUser, $votingUser);
        });
    }
    public function get($userId)
    {
        try {
            $this->_get($userId);
        } catch (Exception $e) {
            return $this->_jsonResponse(array(
                'success' => false,
                'message' => $e->getMessage(),
            ));
        }

        return $this;
    }

    protected function _get($userId)
    {
        if (! $this->_getUsername()) {
            throw new Exception("You have to be logged in to vote.");
        }

        $votingUser = $this->_getContainer()->User()->loadByUsername($this->_getUsername());
        if ($votingUser->getUsername() != 'kalenjordan' && $votingUser->getVoteCount() < 4) {
            throw new Exception("Sorry - you can't vote until you have at least 4 upvotes yourself!");
        }

        $electedUser = $this->_getContainer()->User()->load($userId);

        if ($votingUser->getId() == $electedUser->getId()) {
            throw new Exception("Can't vote for your self.  Nice try, slick.");
        }

        if ($votingUser->hasVotedFor($electedUser->getId())) {
            $electedUser->removeVoteFrom($votingUser->getId());
        } else {
            $electedUser->addVoteFrom($votingUser->getId());
        }

        // Reload to get fresh vote count
        $electedUser = $this->_getContainer()->User()->load($userId);

        $this->_jsonResponse(array(
            'success' => true,
            'vote_count' => $electedUser->getVoteCount(),
        ));
    }
}