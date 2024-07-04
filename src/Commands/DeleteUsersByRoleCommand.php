<?php

namespace Drupal\drush_delete_users\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deletes all users with a specified role.
 *
 * @package Drupal\drush_delete_users\Command
 */
class DeleteUsersByRoleCommand extends DrushCommands {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DeleteUsersByRoleCommand object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
  */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Deletes all users with a specified role.
   *
   * @param string $role
   *   The role to delete users for.
   *
   * @command drush_delete_users:delete-users-by-role
   * @aliases cudur
   * @param string $role The role to delete users for.
   * @usage drush_delete_users:delete-users-by-role editor
   *   Deletes all users with the role 'editor'.
   */
  public function deleteUsersByRole($role) {
    $operations = [];
		$num_operations = 0;
		$batch_id = 1;
    
    $query = $this->entityTypeManager->getStorage('user')->getQuery();
    $uids = $query->condition('status', 1)
              ->condition('roles', $role)
              ->accessCheck(FALSE)
              ->execute();

    if(!empty($uids)) {
      $users = $this->entityTypeManager->getStorage('user')->loadMultiple($uids);
      foreach ($users as $user) {
        $operations[] = [
          '\Drupal\drush_delete_users\BatchServices::deleteUsers',
          [
            $batch_id,
            $user
            ],
          ];
          $num_operations++;
          $batch_id++;        
      }
    }
    else {
      $this->output()->writeln(sprintf('No users found with the role "%s"', $role));
    }
  
    $batch = [
      'title' => t('Testing @num items', ['@num' => $num_operations]),
      'operations' => $operations,
      'finished' => '\Drupal\drush_delete_users\BatchServices::deleteUserFinished',
    ];
    
    batch_set($batch);
    drush_backend_batch_process();
    $this->output()->writeln("Process completed.");
  }

}