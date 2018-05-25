create table b_alutech_exchange_rates
(
     ID int not null auto_increment
  ,  CHARCODE char(10) not null
  ,  NAME char(100) not null
  ,  RATE varchar(255) not null
  ,  ACTIVE char(1) null
  ,  DATE_UPDATE datetime not null
  ,  primary key (ID)
  ,  index IX_ALUTECH_EXCHANGE_RATE_1(ACTIVE)
);

create table b_alutech_exchange_rates_items
(
     ID int not null auto_increment
  ,  PRODUCTS_GROUP char(10) not null
  ,  GROUP_NAME char(10) not null
  ,  NAME varchar(255) not null
  ,  EUR_RATE varchar(255) null
  ,  BYN_RATE varchar(255) null
  ,  DATE_UPDATE timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ,  CODE varchar(255) not null
  ,  primary key (ID)
  ,  unique IX_ALUTECH_EXCHANGE_RATE_CODE_1(CODE)
);

create table b_alutech_exchange_rates_product
(
     ID int not null auto_increment
  ,  NAME varchar(255) not null
  ,  primary key (ID)
  ,  unique IX_ALUTECH_EXCHANGE_RATE_PROD_1(NAME)
);

create table b_alutech_exchange_rates_group
(
     ID int not null auto_increment
  ,  NAME varchar(255) not null
  ,  primary key (ID)
  ,  unique IX_ALUTECH_EXCHANGE_RATE_GR_1(NAME)
);