<?php

declare(strict_types=1);

namespace Forumify\PluginSkeleton\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin pages live behind /admin and are protected by the permissions your plugin
 * declares in its plugin class.
 */
#[Route('/plugin-skeleton', 'plugin_skeleton')]
class ExampleController extends AbstractController
{
    #[Route('', name: '_index')]
    #[IsGranted('plugin-skeleton.admin.example.view')]
    public function __invoke(): Response
    {
        return $this->render('@ForumifyPluginSkeletonPlugin/admin/example.html.twig');
    }
}
