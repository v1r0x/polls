<?php
/**
 * @copyright Copyright (c) 2017 René Gieling <github@dartcafe.de>
 *
 * @author René Gieling <github@dartcafe.de>
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

namespace OCA\Polls\Migration;

use OCP\DB\ISchemaWrapper;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0106Date20201031080745 extends SimpleMigrationStep {

	/** @var IDBConnection */
	protected $connection;

	/** @var IConfig */
	protected $config;

	public function __construct(IDBConnection $connection, IConfig $config) {
		$this->connection = $connection;
		$this->config = $config;
	}

	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		if ($schema->hasTable('polls_share')) {
			$table = $schema->getTable('polls_share');

			if (!$table->hasColumn('display_name')) {
				$table->addColumn('display_name', 'string', [
					'notnull' => false,
					'length' => 64,
					'default' => ''
				]);
			}

			if (!$table->hasColumn('email_address')) {
				$table->addColumn('email_address', 'string', [
					'notnull' => false,
					'length' => 254,
					'default' => ''
				]);
			}
		}

		if ($schema->hasTable('polls_preferences')) {
			$table = $schema->getTable('polls_preferences');
			$table->changeColumn('user_id', [
				'default' => ''
			]);
			$table->changeColumn('preferences', [
				'notnull' => false
			]);
			try {
				$table->addUniqueIndex(['user_id']);
			} catch (\Exception $e) {
				//catch silently, index is already present
			}
		}

		return $schema;
	}

	/**
	 * @return void
	 */
	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
		$schema = $schemaClosure();
		if ($schema->hasTable('polls_share')) {
			$table = $schema->getTable('polls_share');
			if ($table->hasColumn('email_address') && $table->hasColumn('user_email')) {
				$query = $this->connection->getQueryBuilder();
				$query->update('polls_share')
					->set('email_address', 'user_email');
				$query->execute();
			}
		}
	}
}
