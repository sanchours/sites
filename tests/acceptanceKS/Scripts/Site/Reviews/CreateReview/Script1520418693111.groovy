 /**
 * @description Создание отзыва в пользовательской части
 * @step Проверка наличия вкладки "Покупателю" в верхенм меню
 * @step Если вкладка видна:
 * @step - Раскрытие выпадающего списка во вкладке "Покупателю"
 * @step - Переход на страницу "Отзывы"
 * @step - Заполенение обязательного поля "ФИО"
 * @step - Заполнение обязательного поля "E-mail"
 * @step - Заполнение обязательного поля "Ваше мнение"
 * @step - Установка галочки согласия с политикой конфиденциальности
 * @step - Нажатие на кнопку "Отправить"
 * @step Иначе:
 * @step - Ракрытие бургерного меню
 * @step - Раскрытие выпадающего списка во вкладке "Покупателю"
 * @step - Переход на страницу "Отзывы"
 * @step - Заполенение обязательного поля "ФИО"
 * @step - Заполнение обязательного поля "E-mail"
 * @step - Заполнение обязательного поля "Ваше мнение"
 * @step - Установка галочки согласия с политикой конфиденциальности
 * @step - Нажатие на кнопку "Отправить"
 * @step Проверка наличия блока результирующей
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

if (WebUI.verifyElementVisible(findTestObject('Site/CreateReview/a_buyer'), FailureHandling.OPTIONAL)) {
	
	WebUI.focus(findTestObject('Site/CreateReview/a_buyer'))
	
	WebUI.click(findTestObject('Site/CreateReview/a_reviews'))
	
	WebUI.setText(findTestObject('Site/CreateReview/input_name'), 'Test Name')
	
	WebUI.setText(findTestObject('Site/CreateReview/input_email'), 'test@test.te')
	
	WebUI.setText(findTestObject('Site/CreateReview/textarea_content'), 'Отзыв')
	
	WebUI.click(findTestObject('Site/CreateReview/label_form_checkbox_private_policy'))
	
	WebUI.click(findTestObject('Site/TestCallBack/button_send'))
	
} else {
	WebUI.click(findTestObject('Site/CreateReview/div_buyer_sandwich'))

	WebUI.click(findTestObject('Site/CreateReview/div_buyer_list'))

	WebUI.click(findTestObject('Site/CreateReview/a_review_sandwich'))

	WebUI.setText(findTestObject('Site/CreateReview/input_name'), 'Test Name')

	WebUI.delay(10)

	WebUI.setText(findTestObject('Site/CreateReview/input_email'), 'test@test.te')

	WebUI.setText(findTestObject('Site/CreateReview/textarea_content'), 'Отзыв')

	WebUI.click(findTestObject('Site/CreateReview/label_form_checkbox_private_policy'))

	WebUI.click(findTestObject('Site/TestCallBack/button_send'))
}

WebUI.verifyElementVisible(findTestObject('Site/CreateReview/div_response_text'))

