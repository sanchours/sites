import {Button, Col, Form, Icon, Input, Row} from "antd";
import CanapeLogo from "@/components/_sk/CanapeLogo/Index";
import {connect} from "dva";
import React from "react";

const RecoveryForm = ({form, moduleData, dispatch, token}) => {

  const { getFieldDecorator } = form;
  const langValues = moduleData.getIn(['init', 'lang']);

  const handleClickOnButtonEnter = (e) => {
    e.preventDefault();
    dispatch({
      type: 'skGlobal/recoveryPass',
      payload: {
        path: 'out',
        password: form.getFieldValue('password'),
        wpassword: form.getFieldValue('wpassword'),
        token
      }
    });
  };

  return (
    <div className="b-form-wrapper">

      <Form className="login-form">
        <CanapeLogo />

        <h1 className="login-form__header">{langValues.get('passwords_recovery')}</h1>

        <Form.Item className="login-form__item">
          {getFieldDecorator('password', {
            rules: [{ required: false, message: '' }],
          })(
            <Input.Password
              className="login-form__input"
              prefix={<Icon type="lock" style={{ color: 'rgba(0,0,0,.25)' }} />}
              type="password"
              placeholder={langValues.get('new_pass')}
            />,
          )}
        </Form.Item>

        <Form.Item className="login-form__item">
          {getFieldDecorator('wpassword', {
            rules: [{ required: false, message: '' }],
          })(
            <Input.Password
              className="login-form__input"
              prefix={<Icon type="lock" style={{ color: 'rgba(0,0,0,.25)' }} />}
              type="password"
              placeholder={langValues.get('wpassword')}
            />,
          )}
        </Form.Item>

        <Row>
          <Col span={24}>
            <Button
              type="primary"
              className="login-form-button"
              onClick={handleClickOnButtonEnter}
            >
              {langValues.get('send')}
            </Button>
          </Col>
        </Row>
        <Row className="form-forgot">
          <a href="/admin">{langValues.get('back_check')}</a>
        </Row>

      </Form>
    </div>
  );
};

const FormWrapped = Form.create({
  name: 'normal_recovery',
})(RecoveryForm);

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.outLayout.get('out'),
  };
})(FormWrapped);
