<?php


namespace App\Listener;


use App\Command\RecalculateStandingsCommand;
use App\Entity\Match\Match;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

class MatchScoreChangeListener implements AutoloadedEventListener {

    protected ClientInterface $redis;

    public function __construct(ClientInterface $redis) {
        $this->redis = $redis;
    }

    public function getSubscribedEvents() {
        return [Events::preUpdate];
    }

    public function preUpdate(PreUpdateEventArgs $args) {

        $entity = $args->getEntity();
        if ($entity instanceof Match) {
            if (!$args->hasChangedField("status")
                and $entity->getStatus() === Match::FINAL_) {
                if ($args->hasChangedField("homeScore.final") or
                    $args->hasChangedField("awayScore.final")) {
                    $this->redis->push(RecalculateStandingsCommand::CHANGED_FINAL_SCORE, $entity->getSeason()->getId());
                }

            }
        }
    }

}