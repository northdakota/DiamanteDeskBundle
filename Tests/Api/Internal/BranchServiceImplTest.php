<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\DeskBundle\Tests\Api\Internal;

use Diamante\DeskBundle\Api\Internal\BranchServiceImpl;
use Diamante\DeskBundle\Api\Command\BranchCommand;
use Diamante\DeskBundle\Model\Branch\Logo;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Tests\Stubs\UploadedFileStub;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class BranchServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BRANCH_ID = 1;
    const DUMMY_LOGO_PATH = 'uploads/branch/logo';
    const DUMMY_LOGO_NAME = 'dummy-logo-name.png';

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $branchRepository;

    /**
     * @var BranchServiceImpl
     */
    private $branchServiceImpl;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\BranchFactory
     * @Mock \Diamante\DeskBundle\Model\Branch\BranchFactory
     */
    private $branchFactory;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Branch\BranchLogoHandler
     * @Mock \Diamante\DeskBundle\Infrastructure\Branch\BranchLogoHandler
     */
    private $branchLogoHandler;

    /**
     * @var \Oro\Bundle\TagBundle\Entity\TagManager
     * @Mock \Oro\Bundle\TagBundle\Entity\TagManager
     */
    private $tagManager;

    /**
     * @var \Diamante\DeskBundle\Tests\Stubs\UploadedFileStub
     */
    private $fileMock;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\Logo
     * @Mock \Diamante\DeskBundle\Model\Branch\Logo
     */
    private $logo;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\Branch
     * @Mock \Diamante\DeskBundle\Model\Branch\Branch
     */
    private $branch;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->branchServiceImpl = new BranchServiceImpl(
            $this->branchFactory,
            $this->branchRepository,
            $this->branchLogoHandler,
            $this->tagManager,
            $this->securityFacade
        );
    }

    /**
     * @test
     */
    public function thatListsAllBranches()
    {
        $branches = array(new Branch('DUMMY_NAME_1', 'DUMMY_DESC_1'), new Branch('DUMMY_NAME_2', 'DUMMY_DESC_2'));
        $this->branchRepository->expects($this->once())->method('getAll')->will($this->returnValue($branches));

        $retrievedBranches = $this->branchServiceImpl->listAllBranches();

        $this->assertNotNull($retrievedBranches);
        $this->assertTrue(is_array($retrievedBranches));
        $this->assertNotEmpty($retrievedBranches);
        $this->assertEquals($branches, $retrievedBranches);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Branch loading failed. Branch not found.
     */
    public function thatRetreivingExceptionsThrowsExceptionIfBranchDoesNotExists()
    {
        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue(null));
        $this->branchServiceImpl->getBranch(100);
    }

    /**
     * @test
     */
    public function thatRetirevesBranchById()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC');
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $retrievedBranch = $this->branchServiceImpl->getBranch(self::DUMMY_BRANCH_ID);

        $this->assertEquals($branch, $retrievedBranch);
    }

    /**
     * @test
     */
    public function createBranchWithOnlyRequiredValues()
    {
        $name = 'DUMMY_NAME';
        $description = 'DUMMY_DESC';
        $branchStub = new Branch($name, $description, null, new Logo('dummy'));

        $this->branchFactory->expects($this->once())->method('create')
            ->with($this->equalTo($name), $this->equalTo($description))->will($this->returnValue($branchStub));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($branchStub));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;

        $this->branchServiceImpl->createBranch($command);
    }

    /**
     * @test
     */
    public function createBranchWithAllValues()
    {
        $name = 'DUMMY_NAME';
        $description = 'DUMMY_DESC';
        $defaultAssignee = new User();
        $tags = array();
        $branch = new Branch($name, $description, null, new Logo('dummy'));
        $this->fileMock = new UploadedFileStub(self::DUMMY_LOGO_PATH, self::DUMMY_LOGO_NAME);

        $this->branchLogoHandler
            ->expects($this->once())
            ->method('upload')
            ->with($this->equalTo($this->fileMock))
            ->will($this->returnValue($this->fileMock));

        $this->branchFactory->expects($this->once())->method('create')
            ->with(
                $this->equalTo($name), $this->equalTo($description),
                $this->equalTo($defaultAssignee), $this->equalTo($this->fileMock)
            )->will($this->returnValue($branch));

        $this->tagManager->expects($this->once())->method('saveTagging')->with($this->equalTo($branch));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;
        $command->defaultAssignee = $defaultAssignee;
        $command->tags = $tags;
        $command->logoFile = $this->fileMock;

        $this->branchServiceImpl->createBranch($command);
    }

    /**
     * @test
     */
    public function updateBranchWithOnlyRequiredValues()
    {
        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue($this->branch));
        $this->branch->expects($this->never())->method('getLogo');
        $this->branchLogoHandler->expects($this->never())->method('remove');
        $this->branchLogoHandler->expects($this->never())->method('upload');

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';

        $this->branch->expects($this->once())->method('update')
            ->with($this->equalTo($name), $this->equalTo($description));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($this->branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;

        $this->branchServiceImpl->updateBranch($command);
    }

    /**
     * @test
     */
    public function updateBranchWithAllValues()
    {
        $this->fileMock = new UploadedFileStub(self::DUMMY_LOGO_PATH, self::DUMMY_LOGO_NAME);
        $uploadedFile = $this->fileMock->move(self::DUMMY_LOGO_PATH, self::DUMMY_LOGO_NAME);

        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue($this->branch));
        $this->branch->expects($this->exactly(2))->method('getLogo')->will($this->returnValue($this->logo));
        $this->branchLogoHandler->expects($this->once())->method('remove')->with($this->equalTo($this->logo));
        $this->branchLogoHandler->expects($this->once())->method('upload')->with($this->equalTo($this->fileMock))
            ->will($this->returnValue($uploadedFile));

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';
        $defaultAssignee = new User();
        $tags = array();

        $this->branch->expects($this->once())->method('update')->with(
            $this->equalTo($name), $this->equalTo($description), $this->equalTo($defaultAssignee),
            $this->equalTo(new Logo($uploadedFile->getFilename()))
        );

        $this->branch->expects($this->once())->method('setTags')->with($this->equalTo($tags));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($this->branch));

        $this->tagManager->expects($this->once())->method('saveTagging')->with($this->equalTo($this->branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;
        $command->defaultAssignee = $defaultAssignee;
        $command->logoFile = $this->fileMock;
        $command->tags = $tags;

        $this->branchServiceImpl->updateBranch($command);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Branch loading failed, branch not found.
     */
    public function thatBranchDeleteThrowsExceptionIfBranchDoesNotExists()
    {
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue(null));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    /**
     * @test
     */
    public function testDeleteBranchWithLogo()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC', null, new Logo('dummy'));

        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->branchLogoHandler->expects($this->once())->method('remove');

        $this->branchRepository->expects($this->once())->method('remove')->with($this->equalTo($branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    /**
     * @test
     */
    public function testDeleteBranchWithoutLogo()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC');

        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->branchLogoHandler->expects($this->never())
            ->method('remove');

        $this->branchRepository->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($branch));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }
}