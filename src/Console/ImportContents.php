<?php

namespace Nuclear\Hierarchy\Console;

use Nuclear\Hierarchy\Support\ImportHelper;
use Illuminate\Console\Command;

class ImportContents extends Command {

	/**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'hierarchy:import
                            {file : The path to the JSON file for import}
                            {parent=null : The ID of the parent content}
                            {type=null : The default content type id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import content';

    /**
     * Execute the console command.
     *
     * @param ImportHelper $helper
     * @return mixed
     */
    public function handle(ImportHelper $helper)
    {
        // We first read contents
        $contents = $helper->getContentsForImport($this->argument('file'));
        
        if($contents == false) {
            $this->error('The file is not a valid JSON file.');
            return;
        }


        // Then validate the default parent if there is one
        $parent = $this->argument('parent');
        $defaultParent = $helper->getDefaultParent($parent);

        if(!is_null($parent) && is_null($defaultParent)) {
            $this->error('Default parent content not found.');
            return;
        }

        if(!is_null($defaultParent) && $defaultParent->is_sterile) {
            $this->error('Default parent cannot have children content.');
            return;
        }


        // Then validate the default content type if there is one
        $type = $this->argument('type');
        $defaultContentType = $helper->getDefaultContentType($type);

        if(!is_null($type) && is_null($defaultContentType)) {
            $this->error('Default content type not found.');
            return;
        }

        if(is_null($type) && !is_null($defaultParent)) {
            $defaultContentType = $this->getDefaultContentTypeByParent($defaultParent);
        }

        if(!is_null($defaultParent) && !is_null($defaultContentType) && !in_array($getDefaultContentType->id, $defaultParent->contentType->allowed_children_types)) {
            $this->error('Default parent does not accept the default content type.');
            return;
        }


        // We loop through contents to be imported
        foreach($contents as $content) {
            // We first infer the content type
            if(is_null($defaultContentType) && !isset($content['content_type_id'])) {
                $this->line('Skipping: "' . $content['title'] . '" - Type for the content cannot be inferred.');
                continue;
            } elseif(!is_null($defaultContentType) && !isset($content['content_type_id'])) {
                $ct = $defaultContentType;
            } else {
                $ct = $helper->getContentType($content['content_type_id']);

                if(is_null($ct)) {
                    $this->line('Skipping: "' . $content['title'] . '" - Override type for the content cannot be found.');
                    continue;
                }
            }

            // At this stage we know parent is the default parent, which can be null and root as well
            $pId = isset($content['parent_id']) ? $content['parent_id'] : $parent;
            $p = $defaultParent;

            // Override default parent
            if($pId != $parent) $p = $helper->getContent($pId);

            if(!is_null($p)) {
                if($p->is_sterile) {
                    $this->line('Skipping: "' . $content['title'] . '" - Parent cannot have children content.');
                    continue;
                }

                if(!in_array($ct->id, $p->contentType->allowed_children_types)) {
                    $this->line('Skipping: "' . $content['title'] . '" - Parent cannot have children of type "' . $ct->name . '".');
                    continue;
                }
            }

            $content['parent_id'] = $p->id;
            $content['content_type_id'] = $ct->id;                

            $c = $helper->createContent($content);
        }
            
    }

}