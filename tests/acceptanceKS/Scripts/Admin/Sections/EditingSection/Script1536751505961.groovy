/**
 * @description Редактирование раздела
 * @step Нажатие на кнопку "Редактировать"
 * @step Изменение параметров
 * @step Сохранение
 * @step Проверка того, что изменения были внесены 
 */

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable

WebUI.click(findTestObject('CMS/Sections/EditingSection/button_editing_section'))

WebUI.setText(findTestObject('CMS/Tree/NewSection/input_title'), 'Editing section')

WebUI.click(findTestObject('CMS/Tree/NewSection/save'))

WebUI.delay(3)

WebUI.verifyTextPresent('Editing section', false)

