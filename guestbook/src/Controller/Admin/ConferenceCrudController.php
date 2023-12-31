<?php

namespace App\Controller\Admin;

use App\Entity\Conference;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ConferenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Conference::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('city');
        yield TextField::new('year');
        yield BooleanField::new('isInternational');
        yield TextField::new('slug')
            ->onlyOnIndex();
        yield DateField::new('created_at')->hideOnForm();
        yield DateField::new('updated_at')->hideOnForm();
    }
}
