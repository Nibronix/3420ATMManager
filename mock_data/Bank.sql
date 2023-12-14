insert into Bank (Bank_ID, Name, Hours, Phone) values (10, 'Six Flags Entertainment Corporation', '10:59:05', '161-899-4244');
insert into Bank (Bank_ID, Name, Hours, Phone) values (1, 'John Hancock Premium Dividend Fund', '8:29:49', '274-344-1473');
insert into Bank (Bank_ID, Name, Hours, Phone) values (2, 'Blackrock MuniHoldings Quality Fund II, Inc.', '4:36:54', '249-393-7483');
insert into Bank (Bank_ID, Name, Hours, Phone) values (3, 'DBV Technologies S.A.', '20:58:50', '247-331-7069');
insert into Bank (Bank_ID, Name, Hours, Phone) values (4, 'Jewett-Cameron Trading Company', '16:15:32', '641-126-2465');
insert into Bank (Bank_ID, Name, Hours, Phone) values (5, 'Herman Miller, Inc.', '11:56:18', '478-870-5793');
insert into Bank (Bank_ID, Name, Hours, Phone) values (6, 'Marathon Patent Group, Inc.', '21:06:04', '178-230-6455');
insert into Bank (Bank_ID, Name, Hours, Phone) values (7, 'Dynex Capital, Inc.', '16:44:53', '706-682-7508');
insert into Bank (Bank_ID, Name, Hours, Phone) values (8, 'Transportadora De Gas Sa Ord B', '17:16:38', '150-965-7900');
insert into Bank (Bank_ID, Name, Hours, Phone) values (9, 'Five Star Senior Living Inc.', '13:31:35', '375-605-1346');

ALTER TABLE Bank CHANGE COLUMN Hours Start_Hours TIME;
ALTER TABLE Bank ADD Close_Hours TIME;
ALTER TABLE Bank ADD Location VARCHAR(255);

UPDATE Bank SET Start_Hours = SEC_TO_TIME(RAND() * 86400);
UPDATE Bank SET Close_Hours = SEC_TO_TIME(RAND() * 86400);

insert into Bank (Location, Bank_ID) values ('14 Waubesa Terrace', 1);
insert into Bank (Location, Bank_ID) values ('01158 Burning Wood Parkway', 2);
insert into Bank (Location, Bank_ID) values ('9 Memorial Alley', 3);
insert into Bank (Location, Bank_ID) values ('81181 Cottonwood Court', 4);
insert into Bank (Location, Bank_ID) values ('394 Old Gate Avenue', 5);
insert into Bank (Location, Bank_ID) values ('82 Homewood Plaza', 6);
insert into Bank (Location, Bank_ID) values ('2533 Tony Hill', 7);
insert into Bank (Location, Bank_ID) values ('92776 Armistice Street', 8);
insert into Bank (Location, Bank_ID) values ('422 Elgar Junction', 9);
insert into Bank (Location, Bank_ID) values ('704 Warbler Junction', 10);

