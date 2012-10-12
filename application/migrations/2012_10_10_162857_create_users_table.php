<?php

class Create_Users_Table {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
		{
			$table->engine = 'InnoDB';
			$table->integer('id')->unsigned()->primary();
			$table->string('name');
			$table->string('email');
			$table->integer('completed')->default(0);
			$table->date('last_spun')->default('2012-02-26 09:35:00');
		});

		DB::query('ALTER TABLE users CHANGE COLUMN id id BIGINT UNSIGNED');
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
