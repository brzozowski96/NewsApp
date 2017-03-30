<?php

namespace AppBundle\Form;

use AppBundle\Entity\Users;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class, array(
                'label' => 'Login'
            ))
            ->add('password', PasswordType::class, array(
                'label' => 'Hasło'
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Zaloguj się'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Brzozowski\IntelliHomeBundle\Entity\Users'
        ));
    }

    public function getBlockPrefix()
    {
        //return 'brzozowski_blog_bundle_task';
    }
}
