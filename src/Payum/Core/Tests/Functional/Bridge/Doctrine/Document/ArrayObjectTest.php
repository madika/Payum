<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\ArrayObject;

class ArrayObjectTest extends MongoTest
{
    /**
     * @test
     */
    public function shouldAllowPersistEmpty()
    {
        $document = new ArrayObject();
        $this->dm->persist($document);
        $this->dm->flush();

        $this->assertSame([$document], $this->dm->getRepository(ArrayObject::class)->findAll());
    }

    /**
     * @test
     */
    public function shouldAllowPersistWithSomeFieldsSet()
    {
        $model = new ArrayObject();
        $model['foo'] = 'theFoo';
        $model['bar'] = array('bar1', 'bar2' => 'theBar2');

        $this->dm->persist($model);
        $this->dm->flush();

        $this->assertSame([$model], $this->dm->getRepository(ArrayObject::class)->findAll());
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedArrayobject()
    {
        $model = new ArrayObject();
        $model['foo'] = 'theFoo';
        $model['bar'] = array('bar1', 'bar2' => 'theBar2');

        $this->dm->persist($model);
        $this->dm->flush();

        $id = $model->getId();

        $this->dm->clear();

        $foundModel = $this->dm->find(get_class($model), $id);

        //guard
        $this->assertNotSame($model, $foundModel);

        $this->assertEquals(iterator_to_array($model), iterator_to_array($foundModel));
    }

    /**
     * @test
     */
    public function shouldNotStoreSensitiveValue()
    {
        $model = new ArrayObject();
        $model['cardNumber'] = new SensitiveValue('theCardNumber');

        $this->dm->persist($model);
        $this->dm->flush();

        $this->dm->refresh($model);

        if (PHP_VERSION_ID >= 70400) {
            $this->assertEquals(new SensitiveValue(null), $model['cardNumber']);
            $this->assertNotEquals(new SensitiveValue('theCardNumber'), $model['cardNumber']);
        } else {
            $this->assertEquals(null, $model['cardNumber']);
        }
    }
}
