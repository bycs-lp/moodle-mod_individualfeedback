<?php
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
 * Strings for component 'mod_individualfeedback', language 'uk'.
 *
 * @package mod_individualfeedback
 * @copyright @copyright 2022 Natalia Block-Nargan for ISB Bayern, mebis-lernplattform@isb.bayern.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['add_item'] = 'Додати питання';
$string['add_pagebreak'] = 'Додати поділ тексту на сторінки';
$string['adjustment'] = 'Вирівнювання';
$string['after_submit'] = 'Після передачі';
$string['allowfullanonymous'] = 'Дозволити повну анонімність';
$string['analysis'] = 'Обробка';
$string['anonymous'] = 'анонімний';
$string['anonymous_edit'] = 'Заповнити анонімно';
$string['anonymous_entries'] = 'Анонімні записи({$a})';
$string['anonymous_user'] = 'Анонімний користувач';
$string['answerquestions'] = 'Відповісти на запитання';
$string['append_new_items'] = 'Прикріпити до існуючих елементів';
$string['autonumbering'] = 'Автоматична нумерація';
$string['autonumbering_help'] = 'Ця опція активує автоматичну нумерацію усіх питань';
$string['average'] = 'звичайний';
$string['bold'] = 'жирний';
$string['calendarend'] = 'Відгук школяра {$a} закінчується';
$string['calendarstart'] = 'Відгук школяра  {$a} починається';
$string['cannotaccess'] = 'Ви маєте доступ до цього відгуку лише з курсу';
$string['cannotcreatepagebreak'] = 'Розриви сторінок не можна додати на початку відгуку, а в кінці можна додати лише один розрив сторінки';
$string['cannotsavetempl'] = 'Заборонено зберігати шаблони';
$string['captcha'] = 'Captcha';
$string['captchanotset'] = 'Captcha  не вибрана.';
$string['closebeforeopen'] = 'Ви вказали кінцеву дату раніше початкової.';
$string['completed_individualfeedbacks'] = 'Заповнені анкети';
$string['complete_the_form'] = 'Зробити самооцінювання';
$string['completed'] = 'Завершено';
$string['completedon'] = 'Завершено в {$a}';
$string['completiondetail:submit'] = 'надіслати відгук';
$string['completionsubmit'] = 'Дивитися завершеним, коли відгук залишений.';
$string['configallowfullanonymous'] = 'Коли ця опція активована, можна залишити відгук без попередньої реєстрації. Але це стосується виключно відгуків на початковій сторінці.';
$string['confirmdeleteentry'] = 'Ви дійсно бажаєте видалити запис?';
$string['confirmdeleteitem'] = 'Ви дійсно бажаєте видалити цей елемент?';
$string['confirmdeletetemplate'] = 'Ви дійсно бажаєте видалити цей шаблон?';
$string['confirmusetemplate'] = 'Оберіть, будь-ласка, окремі частини (питання або групи питань) для використання або візьміть всю анкету.';
$string['continue_the_form'] = 'Продовжити відгук';
$string['count_of_nums'] = 'Кількість значень';
$string['courseid'] = 'ID курсу';
$string['creating_templates'] = 'Зберегти цю анкету як новий шаблон';
$string['delete_entry'] = 'Видалити запис';
$string['delete_item'] = 'Видалити елемент';
$string['delete_old_items'] = 'Переписати існуючі елементи';
$string['delete_pagebreak'] = 'Видалити поділ тексту на сторінки';
$string['delete_template'] = 'Видалити шаблони';
$string['delete_templates'] = 'Видалити шаблони ...';
$string['depending'] = 'Залежні елементи ';
$string['depending_help'] = 'Залежні елементи дозволяють Вам показувати як елементи пов’язані зі значеннями інших елементів <br />
<strong>Приклад залежних елементів </strong><br />
<ul>
<li>Спочатку створіть елемент, який повинен  залежати від інших.</li>
<li>Зараз додайте поділ тексту на сторінки.</li>
<li>Потім додайте елемент, який  повинен залежати від попередніх значень елементу. Оберіть при створенні формат "Залежний елемент" і встановіть необхідне значення на "Залежному елементі". </li>
</ul>
<strong>Структура повинна виглядати таким чином:</strong>
<ol>
<li> Елемент - запитання: Ви  маєте  авто ? Відповідь: так/ні </li>
<li>Поділ тексту на сторінки </li>
<li>Елемент - запитання: якого кольору Ваше авто? <br />
(Цей елемент посилається на значення \ ´так\´елемента 1)</li>
<li> Елемент - питання: Чому у Вас немає авто? <br />
(Цей елемент посилається на значення \´ні\ елемента 1)</li>
<li> ... інші елементи </li>
</ol>';
$string['dependitem'] = 'Залежний елемент';
$string['dependvalue'] = 'Залежне значення';
$string['description'] = 'Опис';
$string['do_not_analyse_empty_submits'] = 'Ігнорувати пусті передачі';
$string['dropdown'] = 'Окрема відповідь - меню, що випадає';
$string['dropdownlist'] = 'Окрема відповідь - меню, що випадає';
$string['dropdownrated'] = ' Меню, що випадає (масштабоване)';
$string['dropdown_values'] = 'Відповіді';
$string['drop_individualfeedback'] = 'Видалити з цього курсу';
$string['edit_item'] = 'Опрацювати елемент';
$string['edit_items'] = 'Опрацювати елементи';
$string['email_notification'] = 'Надіслати повідомлення при передачі';
$string['email_notification_help'] = 'Якщо ця опція активована, то тренери/тренерки отримують повідомлення при передачі відгуку.';
$string['emailteachermail'] = '{$a->username} відгук: \'{$a->individualfeedback}\' закритий.

Ви можете це тут переглянути:

{$a->url}';
$string['emailteachermailhtml'] = '<p>{$a->username} індивідуальний відгук: <i>\'{$a->individualfeedback}\'</i> закритий.</p>
<p> Відгук <a href="{$a->url}">доступний </a> на веб-сайті.</p>';
$string['entries_saved'] = 'Дякуємо за відгук!';
$string['export_questions'] = 'Експортувати запитання';
$string['export_to_excel'] = 'Експортувати до Excel';
$string['eventresponsedeleted'] = 'Відповідь видалена';
$string['eventresponsesubmitted'] = 'Відправити відповідь';
$string['individualfeedbackcompleted'] = '{$a->username} закритий  {$a->individualfeedbackname}';
$string['individualfeedback:addinstance'] = 'Додати відгук';
$string['individualfeedbackclose'] = 'Відповіді дозволені до';
$string['individualfeedback:complete'] = 'Заповнити відгук';
$string['individualfeedback:createprivatetemplate'] = 'Створити внутрішній шаблон для курсу';
$string['individualfeedback:createpublictemplate'] = 'Створити публічний шаблон';
$string['individualfeedback:deletesubmissions'] = 'Видалити повні записи';
$string['individualfeedback:deletetemplate'] = 'Видалити шаблон';
$string['individualfeedback:deleteprivatetemplate'] = 'Видалити приватні шаблони';
$string['individualfeedback:deletepublictemplate'] = 'Видалити публічні шаблони';
$string['individualfeedback:edititems'] = 'Опрацювати питання';
$string['individualfeedback_is_not_for_anonymous'] = 'Відгук неможливий для анонімних учасників.';
$string['individualfeedback_is_not_open'] = 'Зараз відгук неможливий.';
$string['individualfeedback:mapcourse'] = 'Співвіднести курси з загальними відгуками';
$string['individualfeedbackopen'] = 'Відповіді дозволені від';
$string['individualfeedback:receivemail'] = 'Приймати e-mail-повідомлення';
$string['individualfeedback:view'] = 'Показати відгук';
$string['individualfeedback:viewanalysepage'] = 'Показати сторінку аналізу після передачі';
$string['individualfeedback:viewreports'] = 'Показати обробку';
$string['file'] = 'Файл';
$string['filter_by_course'] = 'Фільтр курсу';
$string['handling_error'] = 'Помилки при Модуль-Дія-Обробка';
$string['hide_no_select_option'] = 'Опція \´не обрано\ приховати';
$string['horizontal'] = 'горизонтально';
$string['check'] = 'Множинний вибір';
$string['checkbox'] = 'Множинний вибір - дозволено декілька варіантів (прапорці)';
$string['check_values'] = 'Можливі рішення';
$string['choosefile'] = 'Обери файл';
$string['chosen_individualfeedback_response'] = 'обрана індивідуальна відгук-відповідь';
$string['downloadresponseas'] = 'Усі відповіді завантажити як:';
$string['importfromthisfile'] = 'Імпортувати з обраного файлу';
$string['import_questions'] = 'Імпортувати питання';
$string['import_successfully'] = 'Успішно імпортований';
$string['includeuserinrecipientslist'] = 'Включити {$a} до списку одержувачів';
$string['indicator:cognitivedepth'] = 'Когнітивний зворотній зв`язок';
$string['indicator:cognitivedepth_help'] = 'Цей індикатор базується на глибині пізнання, досягнутому студентом під час дії зі зворотним зв`язком.';
$string['indicator:cognitivedepthdef'] = 'Когнітивний зворотній зв`язок';
$string['indicator:cognitivedepthdef_help'] = 'Учасник досяг цього відсотка когнітивної активності, запропонованої діяльністю Зворотній зв`язок протягом цього інтервалу аналізу (Рівні = Без перегляду, Перегляд, Надіслати)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'Зворотній зв`язок у соціальних мережах';
$string['indicator:socialbreadth_help'] = 'Цей показник базується на соціальному широті, досягнутому учнем під час діяльності зі зворотного зв`язку.';
$string['indicator:socialbreadthdef'] = 'Зворотній зв`язок у соціальних мережах';
$string['indicator:socialbreadthdef_help'] = 'Учасник досяг цього відсотка соціального залучення, запропонованого заходами зворотного зв`язку протягом цього інтервалу аналізу (Рівні = без участі, учасник сам, учасник з іншими)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['info'] = 'Інформація';
$string['infotype'] = 'Інформація';
$string['insufficient_responses_for_this_group'] = 'Недостатні відповіді для цієї групи';
$string['insufficient_responses'] = 'недостатні відповіді';
$string['insufficient_responses_help'] = 'Щоб  зберегти відгук анонімним, небхідно відправити мінімум дві відповіді залишити.';
$string['item_label'] = 'Опис (необов’язково)';
$string['item_name'] = 'Текст питання';
$string['label'] = 'Поле тексту';
$string['labelcontents'] = 'Вміст';
$string['mapcourseinfo'] = 'Це загальний відгук. Він доступний у всіх курсах, які використовують відгук-блок. Курси, в яких повинен з’явитися відгук можуть бути обмежені через детальне співвіднесення. Для цього  потрібно знайти курс і співвіднести з цим відгуком.';
$string['mapcoursenone'] = 'Не прикріплювати до курсу. Цей відгук доступний у всіх курсах.';
$string['mapcourse'] = 'Співвіднести курс з відгуком';
$string['mapcourse_help'] = 'Як тільки ви обрали відповідні курси, Ви можете співвіднести їх з відгуком. Ви можете обрати курси, натиснувши клавішу Crtl bzw. Cmd під час вибору мишкою. Курс може знову в будь-який час  бути відокремлений від відгука.';
$string['mapcourses'] = 'Співвіднести курси з відгуком';
$string['mappedcourses'] = 'Співвіднесені курси';
$string['mappingchanged'] = 'Структуру курсу змінено.';
$string['minimal'] = 'мінімум';
$string['maximal'] = 'максимум';
$string['messageprovider:message'] = 'Нагадування про відгук';
$string['messageprovider:submission'] = 'Повідомлення про відгук';
$string['mode'] = 'Спосіб';
$string['modulename'] = 'Відгук школяра';
$string['modulename_help'] = 'Активність відгуку школяра це версія активності відгуків, що розвивається.

Вона дозволяє викладачам  запитувати індивідуальні відгуки школярів і порівнювати результати з самооцінюванням. Опитування можна повторити і порівняти їхні результати, щоб представити тимчасовий розвиток.
До цього  викладач може обирати до теми відгуку про заняття з науково розроблених анкет-зразків, ці - за бажанням - пристосувати  до  власних потреб або створити власні нові анкети.

Участь в відгуку учня завжди анонімна, результати доступні лише користувачам, які  зареєстровані як викладачі відповідних курсів.  школярів.

Активність відгуку учня може бути використана для:

* Відгук на урок (перш за все у формі анкет-шаблонів)
* Відгук до змісту курсу, якщо опитування повинне повторитися
* Відгук необхідно порівнювати за різними темами
*Опитування за анонімності гарантоване ';
$string['modulename_link'] = 'модуль/індивідуальний відгук/перегляд';
$string['modulenameplural'] = 'Відгук школяра';
$string['move_item'] = 'Перемістити елемент';
$string['multichoice'] = 'Множинний вибір';
$string['multichoiceoption'] = '<span class="weight">({$a->weight}) </span>{$a->name}';
$string['multichoicerated'] = 'Множинний вибір (масштабований)';
$string['multichoicetype'] = 'Множинний вибір: тип';
$string['multichoice_values'] = 'Множинний вибір: значення';
$string['multiplesubmit'] = 'Багаторазові передачі';
$string['multiplesubmit_help'] = 'Якщо встановлено значення Так:

* Для анонімних опитувань: учасники можуть надсилати необмежену кількість відповідей, і всі відповіді буде записано.
* Для неанонімних опитувань: учасники можуть надсилати необмежену кількість відповідей, але буде записано лише їх останню відповідь.';
$string['name'] = 'Ім’я';
$string['name_required'] = 'Необхідне ім’я';
$string['nameandlabelformat'] = '({$a->label}) {$a->name}';
$string['next_page'] = 'Наступна сторінка';
$string['no_handler'] = 'Не знайдено жодної дії';
$string['no_itemlabel'] = 'Жодного опису';
$string['no_itemname'] = 'Жодного опису запису';
$string['no_items_available_yet'] = 'Ще не визначено жодного елемента';
$string['non_anonymous'] = 'не анонімно';
$string['non_anonymous_entries'] = 'Не анонімні записи ({$a})';
$string['non_respondents_students'] = 'Учасники/учасниці без відповіді ({$a})';
$string['not_completed_yet'] = 'Ще не заповнено';
$string['not_started'] = 'Не почато';
$string['no_templates_available_yet'] = 'Ще не визначено зразків';
$string['not_selected'] = 'Не обрано';
$string['numberoutofrange'] = 'Кількість поза областю';
$string['numeric'] = 'Нумерована відповідь';
$string['numeric_range_from'] = 'Область від';
$string['numeric_range_to'] = 'Область до';
$string['of'] = 'від';
$string['oldvaluespreserved'] = 'Усі старі питання та введені значення збережені.';
$string['oldvalueswillbedeleted'] = 'Актуальні питання та всі відповіді будуть стерті.';
$string['only_one_captcha_allowed'] = 'У відгуку дозволена лише CaptchaIm.';
$string['openafterclose'] = 'Ви вказали дату відкриття після дати закриття';
$string['overview'] = 'Перегляд';
$string['page'] = 'Сторінка';
$string['page-mod-individualfeedback-x'] = 'Кожна сторінка відгуку';
$string['page_after_submit'] = 'Кінцеве повідомлення';
$string['pagebreak'] = 'Поділ тексту на сторінки';
$string['pluginadministration'] = 'Адміністрування відгуку школяра';
$string['pluginname'] = 'Відгук школяра';
$string['position'] = 'Позиція';
$string['previous_page'] = 'Попередня сторінка';
$string['previewquestions'] = 'Попередній перегляд запитань';
$string['privacy:metadata:completed'] = 'Запис про надіслані відгуки';
$string['privacy:metadata:completed:anonymousresponse'] = 'Чи буде надсилання використовуватися анонімно.';
$string['privacy:metadata:completed:timemodified'] = 'Час останньої зміни подання.';
$string['privacy:metadata:completed:userid'] = 'Ідентифікатор користувача, який виконав дію зворотного зв`язку.';
$string['privacy:metadata:completedtmp'] = 'Запис надсилань, які ще тривають.';
$string['privacy:metadata:value'] = 'Запис відповіді на запитання.';
$string['privacy:metadata:value:value'] = 'Вибрана відповідь.';
$string['privacy:metadata:valuetmp'] = 'Запис відповіді на запитання в процесі подання.';
$string['public'] = 'публічно';
$string['question'] = 'Питання';
$string['questionandsubmission'] = 'Налаштування для запитань і записів';
$string['questions'] = 'Питання';
$string['questionslimited'] = 'Лише перші {$a} питання відображаються. Щоб побачити все, покажіть індивідуальні відповіді, або завантажте всю таблицю.';
$string['radio'] = 'Множинний вибір - відповідь';
$string['radio_values'] = 'Відповіді';
$string['ready_individualfeedbacks'] = 'Зворотне повідомлення';
$string['required'] = 'необхідний';
$string['resetting_data'] = 'Перезавантажити відповіді-відгуки.';
$string['resetting_delete'] = 'Видалити відповіді';
$string['resetting_individualfeedbacks'] = 'Відгуки перезавантаженні.';
$string['response_nr'] = 'Відповідь Nr.';
$string['responses'] = 'Відповіді';
$string['responsetime'] = 'Час відповіді';
$string['save_as_new_item'] = 'Зберегти як новий елемент';
$string['save_as_new_template'] = 'Зберегти як новий шаблон';
$string['save_entries'] = 'Зберегти записи';
$string['save_item'] = 'Зберегти елемент';
$string['saving_failed'] = 'Помилки при збереженні';
$string['search:activity'] = 'Індивідуальне зворотне повідомлення - інформація про активацію';
$string['search_course'] = 'Шукати курс';
$string['searchcourses'] = 'Шукати курс';
$string['searchcourses_help'] = 'Шукати за кодом або іменем курсу, який Ви бажаєте співвіднести в цьому відгуку.';
$string['selected_dump'] = 'Архів обраних індексів варіативної сесії:';
$string['send'] = 'Надіслати';
$string['send_message'] = 'Надіслати повідомлення';
$string['show_all'] = 'Показати всі';
$string['show_analysepage_after_submit'] = 'Показати аналізовану сторінку після здачі';
$string['show_entries'] = 'Повідомити про записи';
$string['show_entry'] = 'Показати запис';
$string['show_nonrespondents'] = 'Без відповіді';
$string['site_after_submit'] = 'Сторінка після введення';
$string['sort_by_course'] = 'Сортувати за курсами';
$string['started'] = 'розпочато';
$string['startedon'] = ' розпочато в {$a}';
$string['subject'] = 'Тема';
$string['switch_item_to_not_required'] = 'Установити як непотрібний';
$string['switch_item_to_required'] = 'Установити як необхідний';
$string['template'] = 'Зразок';
$string['templates'] = 'Зразки';
$string['template_deleted'] = 'Стерти зразки';
$string['template_saved'] = 'Зберегти зразки';
$string['textarea'] = 'Область введення';
$string['textarea_height'] = 'Кількість рядків';
$string['textarea_width'] = 'Ширина текстової області';
$string['textfield'] = 'Рядок введення';
$string['textfield_maxlength'] = 'Максимальна кількість символів';
$string['textfield_size'] = 'Ширина текстового поля';
$string['there_are_no_settings_for_recaptcha'] = 'Жодних налаштувань для цієї Captcha.';
$string['this_individualfeedback_is_already_submitted'] = 'Ви вже завершили цю активність.';
$string['typemissing'] = 'Помилкове значення "тип"';
$string['update_item'] = 'Зберегти зміни';
$string['url_for_continue'] = 'URL для кнопки далі';
$string['url_for_continue_help'] = 'Після подання відгуку відображається кнопка "далі" . Стандартно сторінка курсу налаштовується як мета. Якщо Ви бажаєте зробити посилання на інше URL, то Ви можете повідомити ціль для цього.';
$string['use_one_line_for_each_value'] = 'Використовуйте для кожної відповіді новий рядок!';
$string['use_this_template'] = 'Використати зразок';
$string['using_templates'] = 'Обрати зразок';
$string['vertical'] = 'вертикально';
// Deprecated since Moodle 3.1.
$string['cannotmapindividualfeedback'] = 'Проблема з банком даних: індивідуальні зворотні повідомлення не можуть бути співвіднесені з курсом.';
$string['line_values'] = 'Рейтинг';
$string['mapcourses_help'] = 'Once you have selected the relevant course(s) from your search,
you can associate them with this individual feedback using map course(s). Multiple courses may be selected by holding down the Apple or Ctrl key whilst clicking on the course names. A course may be disassociated from a individual feedback at any time.';
$string['max_args_exceeded'] = 'Максимум 6 аргументів можуть розглядатися, забагато аргументів для';
$string['cancel_moving'] = 'Відмінити';
$string['movedown_item'] = 'Перемістити запитання вниз.';
$string['move_here'] = 'Перемістити сюди.';
$string['moveup_item'] = 'Перемістити запитання вгору.';
$string['notavailable'] = 'Цей індивідуальний відгук недоступний.';
$string['saving_failed_because_missing_or_false_values'] = 'Помилково збережено через помилкове або неправильне значення';
$string['cannotunmap'] = 'Проблема з банком даних, співвідношення не можна змінити';
$string['viewcompleted'] = 'Закриті анкети';
$string['viewcompleted_help'] = 'Ви можете переглядати заповнені індивідуальні формуляри відгуків, шукати курс і/ або питання.
Індивідуальні зворотні повідомлення можуть бути експортовані до Excel.';
$string['parameters_missing'] = 'Не вистачає параметрів від';
$string['picture'] = 'Зображення';
$string['picture_file_list'] = 'Список зображень';
$string['picture_values'] = 'Обери один або багато зображень зі списку:';
$string['preview'] = 'Попередній перегляд';
$string['preview_help'] = 'В попередньому перегляді Ви можете змінити послідовність зображень.';
$string['switch_group'] = 'Змінити групу';
$string['separator_decimal'] = '.';
$string['separator_thousand'] = ',';
$string['relateditemsdeleted'] = 'Всі відповіді на це запитання будуть видалені.';
$string['radiorated'] = 'Радіо-кнопка (масштабована)';
$string['radiobutton'] = 'Множинний вибір - окрема відповідь ("радіо-кнопка")';
$string['radiobutton_rated'] = 'Радіо-кнопка (масштабована)';
// Deprecated since Moodle 3.2.
$string['start'] = 'Старт';
$string['stop'] = 'Кінець';

$string['fourlevelapproval'] = '4- рівневий ("не згоден " до "згоден")';
$string['fourlevelapprovaltype'] = 'Тип: 4- рівневий (схвалення)';
$string['fourlevelapproval_options'] = 'не згоден
скоріше  не згоден
скоріше згоден
згоден';
$string['fourlevelfrequency'] = '4-рівневий ("ніколи" до "завжди")';
$string['fourlevelfrequencytype'] = 'Тип: 4-рівневий(частотність)';
$string['fourlevelfrequency_options'] = 'ніколи
інколи
часто
завжди';
$string['fivelevelapproval'] = '5-рівневий "не згоден" до "згоден"';
$string['fivelevelapprovaltype'] = 'Тип: 5-рівневий (згода)';
$string['fivelevelapproval_options'] = 'не згоден
скоріше не згоден
частково
скоріше згоден
згоден';
$string['questiongroup'] = 'Група питань';
$string['questiongroup_name'] = 'Назва групи питань';
$string['edit_questiongroup'] = 'Опрацювати групу питань';
$string['delete_questiongroup'] = 'Видалити групу питань';
$string['end_of_questiongroup'] = 'Кінець групи питань';
$string['confirmdeleteitem_questiongroup'] = 'Ви впевнені, що бажаєте видалити групу питань?
Зверніть увагу, будь-ласка: всі запитання в цій групі будуть видалені.';
$string['move_questiongroup'] = 'Перемістити групу питань';
$string['evaluations'] = 'Оцінювання';
$string['detail_questions'] = 'Деталі (питання)';
$string['detail_groups'] = 'Деталі (групи)';
$string['overview_questions'] = 'Перегляд (питання)';
$string['overview_groups'] = 'Перегляд (групи)';
$string['comparison_questions'] = 'Порівняння (питання)';
$string['comparison_groups'] = 'Порівняння (групи)';
$string['error_subtab'] = 'Не вибрано жодної актуальної вкладки, ця сторінка не завантажується.';
$string['all_results'] = 'Усі результати';
$string['filter_questiongroups'] = 'Фільтрувати групи питань';
$string['individualfeedback:selfassessment'] = 'Самооцінювання';
$string['no_questions_in_group'] = 'Жодних питань у цій групі';
$string['error_calculating_averages'] = 'У цій групі є питання з різною кількістю відповідей. Середнє значення не може бути підраховане.';
$string['analysis_questiongroup'] = 'Групи питань з {$a} запитаннями.';
$string['selfassessment'] = 'Самооцінювання';
$string['average_given_answer'] = 'Середнє значення  поданих відповідей';
$string['duplicate_and_link'] = 'Дублювати і з’єднати';
$string['error_duplicating'] = 'При дублюванні активності можна отримати помилку. Спробуйте знову або зверніться до Вашого системного адміністратора.';
$string['individualfeedback_cloned_and_linked'] = 'Активність відгуку учня дубльована і з’єднана.';
$string['individualfeedback_is_linked'] = 'Ця активність пов’язана з іншими активностями. Тому анкета не може бути опрацьована.';
$string['individualfeedback_not_linked'] = 'Ця активність не пов’язана з іншими активностями.';
$string['individualfeedback_questions_not_equal'] = 'Питання зв’язаних активностей не однакові. Тому їх не можна порівнювати.';
$string['negative_formulated'] = 'Контрольне запитання';
$string['negative_formulated_help'] = 'Контрольні питання - це семантично перекручені питання, негативно сформульовані. Вони вбудовуються в анкети, щоб запобігти фальсифікації результатів через так- ні-тенденцію. Інтегруються контрольні запитання, інвертуються значення відповідей при середньому підрахунку, щоб коректно представляти позитивні або негативні тенденції. <br> .';

$string['privacy:metadata'] = 'Плагін шкільний відгук анонімно зберігає всі відповіді учениць та учнів, так що тут не виникають персональні дані.';

