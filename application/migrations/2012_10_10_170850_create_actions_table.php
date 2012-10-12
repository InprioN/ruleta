<?php

class Create_Actions_Table {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('actions', function($table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('type');
			$table->boolean('completed')->default(false);
			$table->blob('data');
		});

		DB::query('ALTER TABLE actions CHANGE COLUMN user_id user_id BIGINT UNSIGNED');

		Schema::table('actions', function($table)
		{
			$table->foreign('user_id')->references('id')->on('users')->on_delete('cascade');
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('actions');
	}

}
