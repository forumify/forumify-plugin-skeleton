<?php

declare(strict_types=1);

namespace Forumify\PluginSkeleton\Controller;

// skeleton:if entities
use Forumify\PluginSkeleton\Repository\ExampleRepository;
// skeleton:endif
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Routes in src/Controller are loaded from config/routes.yaml, which prefixes their names
 * with forumify_plugin_skeleton_. This one is forumify_plugin_skeleton_index.
 */
class IndexController extends AbstractController
{
    #[Route('/plugin-skeleton', name: 'index')]
    public function __invoke(
        // skeleton:if entities
        ExampleRepository $exampleRepository,
        // skeleton:endif
    ): Response {
        $parameters = [];
        // skeleton:if entities
        $parameters['examples'] = $exampleRepository->findBy([], ['createdAt' => 'DESC'], 25);
        // skeleton:endif

        return $this->render('@ForumifyPluginSkeletonPlugin/frontend/index.html.twig', $parameters);
    }
}
