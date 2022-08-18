/**
 * @description Добавление нового раздела
 * @step Нажатие на кнопку "Добавить" в интерфейсе разделов
 * @step Во всплывающем окне ввод наименования "Test Form"
 * @step Вводим системное имя "test-form"
 * @step Выбираем родительский раздел "Верхнее меню"
 * @step Сохраняем новую секцию
 */
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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

attribute = WebUI.getAttribute(findTestObject('CMS/Tree/frame-sktree'), 'class')

if (attribute.contains('collapsed')) {
    WebUI.click(findTestObject('CMS/Tree/btn_header_tree'))
}

WebUI.click(findTestObject('CMS/Tree/NewSection/add_section'))

WebUI.verifyElementPresent(findTestObject('CMS/Tree/NewSection/add_frame'), 30)

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_title'), 'Test Form')

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_alias'), 'test-form')

WebUI.click(findTestObject('CMS/Tree/NewSection/change_parent_section'))

WebUI.click(findTestObject('CMS/Tree/NewSection/li_top_menu'))

WebUI.click(findTestObject('CMS/Tree/NewSection/save'))
