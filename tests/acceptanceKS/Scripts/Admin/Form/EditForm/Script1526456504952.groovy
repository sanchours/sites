 /**
* @description Редактирование формы
* @step Переход в панель управления
* @step Переход в интерфейс "Формы"
* @step Отключение капчи - пока механика работы с капчей не предусмотрена
* @step Сохранение формы
* @step Проверка наличия созданной формы
* @step Переход на детальную формы
* @step Переход в настройки формы
* @step Внесение изменений в настройки формы (название и пр.)
* @step Сохранение изменений
* @step Переход в списковую часть модуля форм
* @step Проверка того, что изменения были применены
*/ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

NameForm = 'Test Form'

WebUI.click(findTestObject('CMS/Navigate/span_control_panel'))

WebUI.click(findTestObject('CMS/Navigate/panel_form'))

WebUI.click(findTestObject('CMS/Form/ListForm/btn_add'))

WebUI.setText(findTestObject('CMS/Form/SettingsForm/input_form_title'), NameForm)

WebUI.click(findTestObject('CMS/Form/SettingsForm/input_captcha'))

WebUI.click(findTestObject('CMS/Form/SettingsForm/btn_save'))

WebUI.click(findTestObject('CMS/Form/DeleteForm/span_back'))

WebUI.verifyElementVisible(findTestObject('CMS/Form/DeleteForm/div_form_in_list'))

WebUI.click(findTestObject('CMS/Form/EditForm/img_editing'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/span_form_settings'))

WebUI.setText(findTestObject('CMS/Form/SettingsForm/input_form_title'), 'Edited Form')

WebUI.click(findTestObject('CMS/Form/SettingsForm/btn_save'))

WebUI.click(findTestObject('CMS/Form/FormName/span_back'))

WebUI.verifyElementVisible(findTestObject('CMS/Form/EditForm/div_edited_form'), FailureHandling.STOP_ON_FAILURE)

