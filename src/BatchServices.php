<?php

namespace Drupal\drush_delete_users;

/**
 * Batch services for deleting users by role.
 */
class BatchServices {

	public static function deleteUsers($id, $user, &$context) {
		$user->delete();

		$context['results'][] = $user->id();
		$context['message'] = t('Finishing batch @id from user @uid.', [
			'@id' => $id,
			'@uid' => $user->id(),
		]);
	}

	public static function deleteUserFinished($success, array $results) {
		$messenger = \Drupal::messenger();

		if($success) {
			$messenger->addMessage(t('@count users deleted.', ['@count' => count($results)]));
		}
		else {
			$messenger->addMessage(t('Error in the user deletion.'));
		}
	}

}
