% % Algo 1 : create .txt file for intersectyion method
%input: more than 1 .txt file
%output: 1 txt file with common node and modified weight

% To run: 
% filenames = {'a.txt', 'b.txt'} ;          
% intersection(filenames) ;

function  intersection(filenames)
    Z1=load(filenames{1});
    for k = 1 : length(filenames)
        Z2=load(filenames{k});
        Z1=inter(Z1,Z2);
    end
    fileID=fopen('ic_code/intersectionOutput.txt','w');
    fprintf(fileID,'%d %d %f\n',Z1');
    fclose(fileID);
end



