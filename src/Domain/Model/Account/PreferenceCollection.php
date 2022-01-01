<?php

namespace App\Domain\Model\Account;

use Doctrine\Common\Collections\ArrayCollection;

class PreferenceCollection extends ArrayCollection
{
    public function find(string $name): ?string
    {
        foreach ($this as $preference) {
            /** @var Preference $preference */
            if ($preference->getName() === $name) {
                return $preference->getValue();
            }
        }
        return null;
    }
}