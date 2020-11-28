<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2017 Kai Schröer <git@schroeer.co>
 *
 * @author Kai Schröer <git@schroeer.co>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Polls\Tests\Unit\Db;

use OCA\Polls\Db\Poll;
use OCA\Polls\Db\PollMapper;
use OCA\Polls\Db\Option;
use OCA\Polls\Db\OptionMapper;
use OCA\Polls\Tests\Unit\UnitTestCase;
use OCP\IDBConnection;
use League\FactoryMuffin\Faker\Facade as Faker;

class OptionMapperTest extends UnitTestCase {

	/** @var IDBConnection */
	private $con;

	/** @var OptionMapper */
	private $optionMapper;

	/** @var PollMapper */
	private $pollMapper;

	/** @var array */
	private $polls;

	/** @var array */
	private $options;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->con = \OC::$server->getDatabaseConnection();
		$this->optionMapper = new OptionMapper($this->con);
		$this->pollMapper = new PollMapper($this->con);
		$this->polls = [];
		$this->options = [];

		$poll = $this->fm->instance('OCA\Polls\Db\Poll');
		$this->polls[] = $this->pollMapper->insert($poll);
	}

	/**
	 * Create some fake data and persist them to the database.
	 */
	public function testCreate() {
		/** @var Option $option */

		foreach ($this->polls as $poll) {
			$option = $this->fm->instance('OCA\Polls\Db\Option');

			$option->setPollId($poll->getId());
			$this->options[] = $this->optionMapper->insert($option);
			$this->assertInstanceOf(Option::class, $option);
		}

	}

	/**
	 * Find the previously created entries from the database.
	 *
	 * @depends testCreate
	 */
	public function testFind() {
		foreach ($this->options as $option) {
			$this->assertEquals($option, $this->optionMapper->find($option->getId()));
		}
	}
	/**
	 * Find the previously created entries from the database.
	 *
	 * @depends testCreate
	 * @return Option
	 */
	public function testFindByPoll() {
		foreach ($this->options as $option) {
			$this->assertContains($option, $this->optionMapper->findByPoll($option->getId()));
		}
	}

	/**
	 * Update the previously created entry and persist the changes.
	 *
	 * @depends testCreate
	 */
	public function testUpdate() {
		foreach ($this->options as $option) {
			$newPollOptionText = Faker::text(255);
			$option->setPollOptionText($newPollOptionText());
			$this->assertEquals($option, $this->optionMapper->update($option));
		}
	}

	/**
	 * Delete the previously created entries from the database.
	 *
	 * @depends testUpdate
	 */
	public function testDelete() {
		foreach ($this->options as $option) {
			$this->assertInstanceOf(Option::class, $this->optionMapper->delete($option));
		}
	}

	public function tearDown(): void {
		parent::tearDown();
		foreach ($this->polls as $poll) {
			$this->pollMapper->delete($poll);
		}
	}
}
