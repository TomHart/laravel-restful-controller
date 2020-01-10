<?php

namespace TomHart\Restful\Tests;

use ReflectionException;
use TomHart\Restful\Tests\Classes\Models\ModelHasLinksTest;

class HasLinksUnhappyPathsTest extends TestCase
{

    /**
     * A relationship that doesn't implement has links.
     * @throws ReflectionException
     */
    public function testRelationshipDoesntImplementHasLinks(): void
    {
        $model = new ModelHasLinksTest();

        $relationshipLinks = $model->buildRelationshipLinks();

        $this->assertEmpty($relationshipLinks);
    }
}
