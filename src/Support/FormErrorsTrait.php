<?php

namespace App\Support;

use Symfony\Component\Form\FormInterface;

trait FormErrorsTrait
{
    private function flashFormErrors(FormInterface $form): void
    {
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('danger', sprintf('%s: %s', $error->getOrigin()?->getName(), $error->getMessage()));
        }
    }
}