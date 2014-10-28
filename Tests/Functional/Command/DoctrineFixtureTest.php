<?php

namespace Hautelook\AliceBundle\Tests\Functional\Command;

class DoctrineFixtureTest extends AbstractCommandTest
{
    public function testFixture()
    {
        $display = $this->getFixtureDisplay();

        $this->assertContains('> purging database', $display);
        $this->assertContains(
            '> loading Hautelook\AliceBundle\Tests\Functional\TestBundle\DataFixtures\ORM\FixtureLoader1',
            $display
        );
        $this->assertContains(
            '> loading Hautelook\AliceBundle\Tests\Functional\TestBundle\DataFixtures\ORM\FixtureLoader2',
            $display
        );

        $this->verifyProducts();
        $this->verifyBrands();
    }

    private function verifyProducts()
    {
        for ($i = 1; $i <= 10; $i++) {
            /** @var $brand \Hautelook\AliceBundle\Tests\Functional\TestBundle\Entity\Product */
            $product = $this->getDoctrine()->getManager()->find(
                'Hautelook\AliceBundle\Tests\Functional\TestBundle\Entity\Product',
                $i
            );
            $this->assertStringStartsWith('Awesome Product', $product->getDescription());

            // Make sure every product has a brand
            $this->assertInstanceOf(
                'Hautelook\AliceBundle\Tests\Functional\TestBundle\Entity\Brand',
                $product->getBrand()
            );
        }
    }

    private function verifyBrands()
    {
        for ($i = 1; $i <= 10; $i++) {
            /** @var $brand \Hautelook\AliceBundle\Tests\Functional\TestBundle\Entity\Brand */
            $this->getDoctrine()->getManager()->find(
                'Hautelook\AliceBundle\Tests\Functional\TestBundle\Entity\Brand',
                $i
            );
        }
    }

    protected function getCommands()
    {
        return array();
    }
}
