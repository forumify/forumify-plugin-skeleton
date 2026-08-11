import { Controller } from '@hotwired/stimulus';

/*
 * Stimulus controllers listed in package.json are picked up by forumify automatically.
 * Use one from a template with stimulus_controller(), where the identifier is your npm
 * package name and this file's name joined by double dashes, for example:
 *
 *     <div {{ stimulus_controller('acme--todo-plugin--example') }}></div>
 */
export default class extends Controller {
    static targets = ['output'];

    connect() {
        if (this.hasOutputTarget) {
            this.outputTarget.textContent = 'Hello from Plugin Skeleton.';
        }
    }
}
