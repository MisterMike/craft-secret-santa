<?php
namespace nibiru\secretsanta\variables;

use nibiru\secretsanta\SecretSanta;

class SecretSantaVariable
{
    public function groups()
    {
        return SecretSanta::$plugin->group->getAll();
    }

    public function members(int $groupId)
    {
        return SecretSanta::$plugin->member->getMembersByGroup($groupId);
    }
}
