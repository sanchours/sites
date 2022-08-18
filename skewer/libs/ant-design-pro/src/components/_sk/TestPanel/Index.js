import React from 'react';
import {Col, Divider, Row, Spin} from 'antd';
import styles from './style.less';
import DockedItem from "../DockedItem/Index";

export default ({ title, buttonsData, handleClickOnButton, children, selectedRows }) => {
  const buildButtons = () => {

    if (!buttonsData){
      return null;
    }

    const btnData = buttonsData.toJS();

    let buttonsComponents = [];

    for (const index in btnData){
      const value = btnData[index];
      const {action, state, text} = value;


      if ( typeof value !== 'object' ){
        buttonsComponents = [
          ...buttonsComponents,
          <Divider
            className={value === '->' ? 'divider-grow' : ''}
            key={`divider_${index}`}
          />
        ];
      } else {
        buttonsComponents = [
          ...buttonsComponents,
          <DockedItem
            key={`${action}_${state}_${index}`}
            configButton={value}
            selectedRows={selectedRows}
            handleClickOnButton={handleClickOnButton(value)}
            text={text}
          />
        ];
      }
    }

    return buttonsComponents;

  };

  return (
    <Row className="panel">
      {
        title ? (
          <Row>
            <Col className="panel__header" span={24}>
              {title}
            </Col>
          </Row>
        ) : null
      }
      <Row type="flex">
        {
          buttonsData ? (
            <>
              <Col className="panel__leftColumn" xs={24} sm={24}>
                {buildButtons()}
              </Col>
              <Col className="panel__centerColumn" xs={24} sm={24}>
                {children}
              </Col>
            </>
          ) : (
            <Col className="panel__centerColumn panel__centerColumn--alone">
              {children}
            </Col>
          )
        }
      </Row>
    </Row>
  );

}
