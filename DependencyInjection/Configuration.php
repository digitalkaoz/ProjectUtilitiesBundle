<?php

namespace rs\ProjectUtilitiesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * a simple yaml to bundle wrapper
 * 
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @package rs.ProjectUtitlitiesBundle
 * @subpackage DepedencyInjection
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\NodeInterface
     */
    public function getConfigTree($kernelDebug)
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('project_utilities', 'array');

        $rootNode
                ->variableNode('bootstrap')->end()
                ;
                
        return $treeBuilder->buildTree();
    }
}
