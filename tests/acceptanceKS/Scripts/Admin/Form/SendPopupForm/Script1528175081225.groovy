 /**
 * @description Отправка всплывающей формы
 * @step Присвоение формы к разделу
 * @step Переход в раздел
 * @step Открытие генератора форм
 * @step Установка значения ID раздела с формой
 * @step Сохранение 
 * @step Переход на страницу со всплывающей формой
 * @step Открытие всплывающей формы
 * @step Заполнение полей
 * @step Отправка формы
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import data.FormData as FormData
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW

FormData DataForm = new FormData()

WebUI.click(findTestObject('CMS/TabSection/div_tab_form'))

WebUI.click(findTestObject('CMS/TabSection/select_form'))

WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/li_form_with_all_fields'))

WebUI.click(findTestObject('Object Repository/CMS/TabSection/save_form_section'))

WebUI.mouseOver(findTestObject('CMS/Form/SendPopupForm/tr_id_form'))

idSection = WebUI.getText(findTestObject('CMS/Form/SendPopupForm/div_id_section'), FailureHandling.STOP_ON_FAILURE)

WebUI.delay(2)

WebUI.click(findTestObject('CMS/Form/SendPopupForm/div_section_with_popup_form'))

WebUI.click(findTestObject('CMS/Form/SendPopupForm/span_editor'))

WebUI.click(findTestObject('CMS/Form/SendPopupForm/a_form_generator'))

WebUI.setText(findTestObject('CMS/Form/SendPopupForm/input_id_section_form'), idSection)

WebUI.click(findTestObject('CMS/Form/SendPopupForm/button_ok'))

WebUI.click(findTestObject('CMS/Form/SendPopupForm/span_save'))

WebUI.delay(3)

WebUI.click(findTestObject('CMS/TabSection/section_link'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('CMS/Form/SendPopupForm/a_popup_form'))

WebUI.click(findTestObject('CMS/Form/SendPopupForm/select_drop_down_list'), FailureHandling.STOP_ON_FAILURE)

WebUI.click(findTestObject('CMS/Form/SendPopupForm/div_value_2_drop_down'))

WebUI.click(findTestObject('CMS/Form/SendPopupForm/label_checkbox'))

WebUI.click(findTestObject('CMS/Form/SendPopupForm/label_value_2_checkbox_group'))

WebUI.click(findTestObject('CMS/Form/SendPopupForm/label_value_2_radiogroup'))

WebUI.setText(findTestObject('CMS/Form/SendPopupForm/input_kalendar'), DataForm.date)

WebUI.setText(findTestObject('CMS/Form/SendPopupForm/input_multiline_text_field'), DataForm.multilinetext)

WebUI.click(findTestObject('CMS/Form/SendPopupForm/input_multiline_text_field'), FailureHandling.STOP_ON_FAILURE)

WebUI.click(findTestObject('CMS/Form/SendPopupForm/li_rating_2'))

WebUI.setText(findTestObject('CMS/Form/SendPopupForm/input_password'), DataForm.password)

WebUI.setText(findTestObject('CMS/Form/SendPopupForm/input_text_field'), DataForm.text)

/**WebDriver driver = DriverFactory.getWebDriver()

ArrayList<WebElement> listTypeField = driver.findElements(By.cssSelector('.form__col-1'))

int countListTypeField = listTypeField.size()

for (int numberField = 1; numberField <= (countListTypeField - 2); numberField++) {
    switch (numberField) {
        case 1:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/span_drop_down_list'))

            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/li_value_2_drop_down_list'))

            break
        case 2:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_checkbox'))

            break
        case 3:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_value_2_checkbox_group'))

            break
        case 4:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_value_2_radiogroup'))

            break
        case 5:
        case 6:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_calendar'), DataForm.date)

            break
        case 7:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_multiline_text_field'), DataForm.multilinetext)

            break
        case 8:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_password'), DataForm.password)

            break
        case 9:
            WebUI.verifyElementVisible(findTestObject('CMS/Form/CheckSendFormOnPage/div_delimiter'))

            break
        case 10:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/div_value_2_rating'))

            break
        case 11:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_text_field'), DataForm.text)

            break
    }
}*/
WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_checkbox_private_policy'))

WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/button_send'))

