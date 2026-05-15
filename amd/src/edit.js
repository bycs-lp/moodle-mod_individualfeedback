// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edit items in individualfeedback module
 *
 * @module     mod_individualfeedback/edit
 * @copyright  2016 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import $ from 'jquery';
import {addIconToContainerRemoveOnCompletion} from 'core/loadingicon';
import Notification from 'core/notification';
import Pending from 'core/pending';
import {prefetchStrings} from 'core/prefetch';
import SortableList from 'core/sortable_list';
import {getString, getStrings} from 'core/str';
import {add as addToast} from 'core/toast';
import {reorderQuestions} from 'mod_individualfeedback/local/repository';
import Templates from 'core/templates';

const Selectors = {
    deleteQuestionButton: '[data-action="delete"]',
    sortableListRegion: '[data-region="questions-sortable-list"]',
    sortableElement: '[data-region="questions-sortable-list"] .individualfeedback_itemlist[id]',
    sortableElementTitle: '[data-region="item-title"]',
    questionLabel: '[data-region="questions-sortable-list"] .col-form-label',
    actionsMenuData: '[data-item-actions-menu]',
    questionGroupName: '.individualfeedback_questiongroup',
    questionGroupEnd: '.individualfeedback_questiongroupend',
};

/**
 * Returns the individualfeedback question item id from the DOM id of an item.
 *
 * @param {String} id The dom id, f.g.: individualfeedback_item_22
 * @return int
 */
const getItemId = (id) => {
    return Number(id.replace(/^.*individualfeedback_item_/i, ''));
};

/**
 * Returns the order of the items in the sortable list.
 *
 * @param {Element} element The element to get the order from.
 * @return string
 */
const getItemOrder = (element) => {
    const sortableList = element.closest(Selectors.sortableListRegion);
    let itemOrder = [];
    sortableList.querySelectorAll(Selectors.sortableElement).forEach((item) => {
        itemOrder.push(getItemId(item.id));
    });
    return itemOrder.toString();
};

let initialized = false;
let moduleId = null;

/**
 * Initialise editor and all it's modules
 *
 * @param {Integer} cmId
 */
export const init = async(cmId) => {

    moduleId = cmId;

    // Ensure we only add our listeners once (can be called multiple times).
    if (initialized) {
        return;
    }

    prefetchStrings('core', [
        'yes',
        'no',
    ]);
    prefetchStrings('admin', [
        'confirmation',
    ]);
    prefetchStrings('mod_individualfeedback', [
        'confirmdeleteitem',
        'questionmoved',
        'move_item',
        'end_of_questiongroup',
    ]);

    await enhanceEditForm();

    // Add event listeners.
    document.addEventListener('click', async event => {

        // Delete question.
        const deleteButton = event.target.closest(Selectors.deleteQuestionButton);
        if (deleteButton) {
            event.preventDefault();
            const confirmationStrings = await getStrings([
                {key: 'confirmation', component: 'admin'},
                {key: 'confirmdeleteitem', component: 'mod_individualfeedback'},
                {key: 'yes', component: 'core'},
                {key: 'no', component: 'core'},
            ]);
            Notification.confirm(...confirmationStrings, () => {
                window.location = deleteButton.getAttribute('href');
            });
            return;
        }
    });

    // Initialize sortable list to handle active conditions moving.
    const sortableList = new SortableList(document.querySelector(Selectors.sortableListRegion));
    sortableList.getElementName = (element) => {
        const el = element[0];
        const titleRegion = el.querySelector(Selectors.sortableElementTitle);
        let name = titleRegion?.textContent?.trim() || '';
        if (!name && titleRegion) {
            const groupName = titleRegion.querySelector(Selectors.questionGroupName);
            if (groupName) {
                name = groupName.textContent?.trim() || '';
            }
            if (!name) {
                const groupEnd = titleRegion.querySelector(Selectors.questionGroupEnd);
                if (groupEnd) {
                    name = groupEnd.textContent?.trim() || '';
                }
            }
        }
        if (!name && el.querySelector(Selectors.questionGroupEnd)) {
            return getString('end_of_questiongroup', 'mod_individualfeedback');
        }
        return Promise.resolve(name);
    };

    $(Selectors.sortableListRegion).on(SortableList.EVENTS.DROP, (event, info) => {
        if (!info.positionChanged) {
            return;
        }
        const pendingPromise = new Pending('mod_individualfeedback/questions:reorder');
        const itemOrder = getItemOrder(info.element[0]);
        addIconToContainerRemoveOnCompletion(info.element[0], pendingPromise);
        reorderQuestions(moduleId, itemOrder)
            .then(() => getString('questionmoved', 'mod_individualfeedback'))
            .then(addToast)
            .then(() => pendingPromise.resolve())
            .catch(Notification.exception);
    });

    initialized = true;
};

/**
 * Enhance the edit form by adding a move item button and an action menu to each question.
 *
 * @returns {Promise<void>}
 */
const enhanceEditForm = async() => {
    const questionLabels = document.querySelectorAll(Selectors.questionLabel);
    const movetitle = await getString('move_item', 'mod_individualfeedback');

    const updates = Array.from(questionLabels).map(async(container) => {
        const label = container.querySelector(Selectors.actionsMenuData);
        if (!label) {
            return;
        }

        try {
            const contextData = {
                movetitle,
                label: label.parentElement.outerHTML,
                actionsmenu: JSON.parse(label.dataset.itemActionsMenu || '{}'),
            };
            container.innerHTML = await Templates.render('mod_individualfeedback/item_edit_enhanced_title', contextData);
        } catch (error) {
            await Notification.exception(error);
        }
    });
    await Promise.all(updates);
};
