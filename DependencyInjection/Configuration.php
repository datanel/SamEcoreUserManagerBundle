<?php

namespace CanalTP\SamEcoreUserManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sam_user');

        $rootNode->children()
            ->scalarNode('users_by_page')->defaultValue(20)->end()
            ->end();

        $this->addFormSection($rootNode);

        return $treeBuilder;
    }

    private function addFormSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('form')
                    ->children()
                        ->arrayNode('user')
                            ->canBeUnset()
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->defaultValue('text')->end()
                                    ->arrayNode('options')
                                        ->children()
                                            ->scalarNode('label')->end()
                                            ->arrayNode('attr')
                                                ->children()
                                                    ->scalarNode('class')->end()
                                                    ->scalarNode('placeholder')->end()
                                                ->end()
                                            ->end()
                                            ->scalarNode('translation_domain')->defaultValue('corebo')->end()
                                        ->end()
                                    ->end()

                                ->end()
                            ->end()
                            ->defaultValue(
                                array(
                                    'firstname' => array (
                                        'type' => 'text',
                                        'options' => array (
                                            'attr' => array (
                                                'class' => 'col-md-4',
                                                'placeholder' => 'enter username'
                                            ),
                                            'translation_domain' => 'corebo'
                                        )
                                    ),
                                    'lastname' => array (
                                        'type' => 'text',
                                        'options' => array (
                                            'attr' => array (
                                                'class' => 'col-md-4',
                                                'placeholder' => 'enter username'
                                            ),
                                            'translation_domain' => 'corebo'
                                        )
                                    ),
                                    'email' => array (
                                        'type' => 'text',
                                        'options' => array (
                                            'attr' => array (
                                                'class' => 'col-md-4',
                                                'placeholder' => 'enter username'
                                            ),
                                            'translation_domain' => 'corebo'
                                        )
                                    )
                                )
                            )
                        ->end()

                        ->arrayNode('registration')
                            ->canBeUnset()
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->defaultValue('text')->end()
                                    ->arrayNode('options')
                                        ->children()
                                            ->scalarNode('label')->end()
                                            ->scalarNode('type')->end()
                                            ->booleanNode('allow_add')->end()
                                            ->booleanNode('allow_delete')->end()
                                            ->booleanNode('by_reference')->end()
                                            ->arrayNode('options')
                                                ->children()
                                                    ->booleanNode('required')->end()
                                                    ->booleanNode('error_bubbling')->end()
                                                    ->arrayNode('attr')
                                                        ->children()
                                                            ->scalarNode('class')->end()
                                                            ->scalarNode('placeholder')->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                            ->arrayNode('attr')
                                                ->children()
                                                    ->scalarNode('class')->end()
                                                    ->scalarNode('placeholder')->end()
                                                ->end()
                                            ->end()
                                            ->scalarNode('translation_domain')->defaultValue('corebo')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->defaultValue(
                                array(
                                    'username' => array(
                                        'type' => 'text',
                                        'options' => array(
                                            'label' => 'form.username',
                                            'attr' => array(
                                                'class' => 'col-md-4',
                                                'placeholder' => 'enter username'
                                            ),
                                            'translation_domain' => 'FOSUserBundle'
                                        )
                                    ),
                                    'email' => array(
                                        'type' => 'email',
                                        'options' => array(
                                            'label' => 'form.email',
                                            'attr' => array(
                                                'class' => 'col-md-4',
                                                'placeholder' => 'enter email'
                                            ),
                                            'translation_domain' => 'FOSUserBundle'
                                        )
                                    )
                                )
                            )
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
