 /**
  * @description Создание нового пользователя
 * @step Переход на панель упаравления
 * @step Переход на вкладку "Пользователи"
 * @step Нажатие на кнопку "Добавить"
 * @step Заполнение поля "Логин"
 * @step Заполнение поля "Пароль"
 * @step Заполнение поля "Повторите пароль"
 * @step Выбор политики доступа
 * @step Заполнение поля "Е-mail пользователя"
 * @step Нажатие на кнопку "Сохранить"
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
import org.openqa.selenium.Keys as Keys
import urlSite.BaseLink as BaseLink

WebUI.click(findTestObject('CMS/SignUp/div_control_panel'))

WebUI.click(findTestObject('CMS/SignUp/div_user'))

WebUI.click(findTestObject('CMS/SignUp/button_create'))

WebUI.setText(findTestObject('CMS/SignUp/input_user_login'), 'demianov@web-canape.com')

WebUI.setText(findTestObject('CMS/SignUp/input_user_pass'), '123456')

WebUI.setText(findTestObject('CMS/SignUp/input_user_pass_repeat'), '123456')

WebUI.click(findTestObject('CMS/SignUp/input_UserType'))

WebUI.click(findTestObject('CMS/SignUp/li_operator'))

WebUI.setText(findTestObject('CMS/SignUp/input_email'), 'demianov@web-canape.com')

WebUI.click(findTestObject('CMS/SignUp/button__save'))

